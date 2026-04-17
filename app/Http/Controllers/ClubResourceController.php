<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveClubResourceRequest;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\Plan;
use App\Support\ResourceTypeCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubResourceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ClubResource::class);

        $user = $request->user();
        $search = trim((string) $request->string('q'));
        $typeFilter = trim((string) $request->string('type')) ?: null;
        $selectedBranchId = $user->isAdminBranch()
            ? $user->branch_id
            : ($request->filled('branch_id') ? $request->integer('branch_id') : null);
        $query = ClubResource::query()->with(['branch', 'plans', 'schedules', 'blocks']);

        if ($user->isAdminBranch()) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $typeOptionSnapshot = (clone $query)->get();

        if ($typeFilter) {
            $this->applyTypeFilter($query, $typeFilter);
        }

        $resourceSnapshot = (clone $query)->get();

        return view('resources.index', [
            'resources' => $query
                ->orderBy('branch_id')
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString(),
            'branches' => $user->isAdminMatrix()
                ? Branch::query()->active()->orderBy('name')->get()
                : Branch::query()->active()->whereKey($user->branch_id)->orderBy('name')->get(),
            'selectedBranch' => $selectedBranchId ? Branch::query()->find($selectedBranchId) : null,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'typeOptions' => ResourceTypeCatalog::options($typeOptionSnapshot),
            'summary' => [
                'resources' => $resourceSnapshot->count(),
                'active' => $resourceSnapshot->where('is_active', true)->count(),
                'branches' => $selectedBranchId ? 1 : max($resourceSnapshot->pluck('branch_id')->filter()->unique()->count(), 1),
                'avgCapacity' => (int) round($resourceSnapshot->avg('max_capacity') ?? 0),
                'blocked' => $resourceSnapshot->sum(fn (ClubResource $resource) => $resource->blocks->count()),
            ],
            'branchSummaries' => $resourceSnapshot
                ->groupBy(fn (ClubResource $resource) => $resource->branch?->name ?? 'Sem filial')
                ->map(fn ($group, $branchName) => [
                    'id' => $group->first()?->branch_id,
                    'name' => $branchName,
                    'resources' => $group->count(),
                    'active' => $group->where('is_active', true)->count(),
                    'plans' => $group->sum(fn (ClubResource $resource) => $resource->plans->count()),
                    'types' => ResourceTypeCatalog::options($group)->count(),
                ])
                ->values(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', ClubResource::class);

        return view('resources.form', [
            'clubResource' => new ClubResource([
                'branch_id' => $request->integer('branch_id'),
                'is_active' => true,
            ]),
            'branches' => $request->user()->isAdminMatrix()
                ? Branch::query()->active()->orderBy('name')->get()
                : Branch::query()->whereKey($request->user()->branch_id)->get(),
            'plans' => Plan::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(SaveClubResourceRequest $request)
    {
        $this->authorize('create', ClubResource::class);
        $this->guardBranchScope($request);

        $clubResource = ClubResource::query()->create([
            'branch_id' => $request->validated('branch_id'),
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'type' => $request->validated('type'),
            'description' => $request->validated('description'),
            'max_capacity' => $request->validated('max_capacity'),
            'default_price' => $request->validated('default_price'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncSchedules($clubResource, $request->validated('schedules'));
        $clubResource->plans()->sync($request->validated('allowed_plan_ids', []));
        $this->maybeCreateBlock($clubResource, $request);

        return redirect()->route('recursos.index')->with('status', 'Recurso cadastrado com sucesso.');
    }

    public function edit(ClubResource $clubResource, Request $request)
    {
        $clubResource->load(['plans', 'schedules', 'blocks']);
        $this->authorize('update', $clubResource);

        return view('resources.form', [
            'clubResource' => $clubResource,
            'branches' => $request->user()->isAdminMatrix()
                ? Branch::query()->active()->orderBy('name')->get()
                : Branch::query()->whereKey($request->user()->branch_id)->get(),
            'plans' => Plan::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(SaveClubResourceRequest $request, ClubResource $clubResource)
    {
        $this->authorize('update', $clubResource);
        $this->guardBranchScope($request);

        $clubResource->update([
            'branch_id' => $request->validated('branch_id'),
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'type' => $request->validated('type'),
            'description' => $request->validated('description'),
            'max_capacity' => $request->validated('max_capacity'),
            'default_price' => $request->validated('default_price'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncSchedules($clubResource, $request->validated('schedules'));
        $clubResource->plans()->sync($request->validated('allowed_plan_ids', []));
        $this->maybeCreateBlock($clubResource, $request);

        return redirect()->route('recursos.edit', $clubResource)->with('status', 'Recurso atualizado com sucesso.');
    }

    protected function syncSchedules(ClubResource $clubResource, array $schedules): void
    {
        $clubResource->schedules()->delete();
        $clubResource->schedules()->createMany($schedules);
    }

    protected function maybeCreateBlock(ClubResource $clubResource, SaveClubResourceRequest $request): void
    {
        if ($request->filled('block_date') && $request->filled('block_start_time') && $request->filled('block_end_time')) {
            $clubResource->blocks()->create([
                'branch_id' => $clubResource->branch_id,
                'block_date' => $request->validated('block_date'),
                'start_time' => $request->validated('block_start_time'),
                'end_time' => $request->validated('block_end_time'),
                'reason' => $request->validated('block_reason'),
                'blocked_by_user_id' => $request->user()->id,
            ]);
        }
    }

    protected function guardBranchScope(SaveClubResourceRequest $request): void
    {
        if ($request->user()->isAdminBranch() && $request->integer('branch_id') !== $request->user()->branch_id) {
            abort(403);
        }
    }

    protected function applyTypeFilter($query, string $selectedType): void
    {
        if ($selectedType === 'outros') {
            $query->where(function ($builder) {
                $builder
                    ->whereNull('type')
                    ->orWhereRaw("TRIM(COALESCE(type, '')) = ''");
            });

            return;
        }

        $query->whereRaw(
            "LOWER(TRIM(REPLACE(REPLACE(COALESCE(type, ''), '_', ' '), '-', ' '))) = ?",
            [ResourceTypeCatalog::queryValue($selectedType)]
        );
    }
}
