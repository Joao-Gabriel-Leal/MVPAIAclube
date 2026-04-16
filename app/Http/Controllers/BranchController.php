<?php

namespace App\Http\Controllers;

use App\Enums\BranchType;
use App\Http\Requests\SaveBranchRequest;
use App\Models\Branch;
use App\Services\BillingService;
use App\Services\BranchHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Branch::class);

        return view('branches.index', [
            'branches' => Branch::query()
                ->withCount([
                    'members as active_members_count' => fn ($query) => $query->where('status', 'active'),
                    'members as pending_members_count' => fn ($query) => $query->where('status', 'pending'),
                    'resources',
                    'dependents',
                ])
                ->latest()
                ->paginate(12),
            'summary' => [
                'branches' => Branch::query()->count(),
                'active' => Branch::query()->where('is_active', true)->count(),
                'members' => Branch::query()->withCount('members')->get()->sum('members_count'),
                'resources' => Branch::query()->withCount('resources')->get()->sum('resources_count'),
            ],
        ]);
    }

    public function show(Request $request, Branch $branch, BranchHubService $branchHubService, BillingService $billingService)
    {
        $this->authorize('view', $branch);

        $billingService->refreshOverdueStatuses();

        $tab = $this->resolveTab($request->string('tab')->toString());

        return view('branches.show', [
            'branch' => $branch,
            'tab' => $tab,
            'tabs' => collect([
                'resumo' => 'Resumo',
                'planos' => 'Planos',
                'recursos' => 'Recursos',
                'reservas' => 'Reservas',
                'financeiro' => 'Financeiro',
                'relatorios' => 'Relatorios',
            ]),
            ...$branchHubService->build($branch, $request->user(), $request->only(['start_date', 'end_date', 'status'])),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Branch::class);

        return view('branches.form', [
            'branch' => new Branch([
                'type' => BranchType::Branch,
            ]),
            'branchTypes' => BranchType::cases(),
        ]);
    }

    public function store(SaveBranchRequest $request)
    {
        $this->authorize('create', Branch::class);

        Branch::query()->create([
            ...$request->validated(),
            'slug' => Str::slug($request->validated('slug')),
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);

        return redirect()->route('filiais.index')->with('status', 'Filial cadastrada com sucesso.');
    }

    public function edit(Branch $branch)
    {
        $this->authorize('update', $branch);

        return view('branches.form', [
            'branch' => $branch,
            'branchTypes' => BranchType::cases(),
        ]);
    }

    public function update(SaveBranchRequest $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $branch->update([
            ...$request->validated(),
            'slug' => Str::slug($request->validated('slug')),
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);

        return redirect()->route('filiais.index')->with('status', 'Filial atualizada com sucesso.');
    }

    protected function resolveTab(?string $tab): string
    {
        $allowedTabs = ['resumo', 'planos', 'recursos', 'reservas', 'financeiro', 'relatorios'];

        return in_array($tab, $allowedTabs, true) ? $tab : 'resumo';
    }
}
