<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePlanRequest;
use App\Models\ClubResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Plan::class);

        return view('plans.index', [
            'plans' => Plan::query()
                ->with(['resources.branch'])
                ->withCount(['members', 'resources'])
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'canManagePlans' => $request->user()->can('create', Plan::class),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Plan::class);

        return view('plans.form', $this->formViewData(new Plan([
            'is_active' => true,
            'dependents_inherit_benefits' => false,
        ])));
    }

    public function store(SavePlanRequest $request)
    {
        $this->authorize('create', Plan::class);

        $plan = Plan::query()->create($this->planAttributes($request));
        $plan->resources()->sync($request->validated('resource_ids', []));

        return redirect()->route('plans.edit', $plan)->with('status', 'Plano cadastrado com sucesso.');
    }

    public function edit(Plan $plan)
    {
        $this->authorize('update', $plan);

        return view('plans.form', $this->formViewData($plan));
    }

    public function update(SavePlanRequest $request, Plan $plan)
    {
        $this->authorize('update', $plan);

        $plan->update($this->planAttributes($request, $plan));
        $plan->resources()->sync($request->validated('resource_ids', []));

        return redirect()->route('plans.edit', $plan)->with('status', 'Plano atualizado com sucesso.');
    }

    public function destroy(Plan $plan)
    {
        $this->authorize('delete', $plan);

        if ($plan->members()->exists()) {
            return redirect()
                ->route('plans.edit', $plan)
                ->withErrors(['delete' => 'Nao e possivel excluir um plano com associados vinculados.']);
        }

        $plan->delete();

        return redirect()->route('plans.index')->with('status', 'Plano excluido com sucesso.');
    }

    protected function formViewData(Plan $plan): array
    {
        if ($plan->exists) {
            $plan->loadMissing(['resources.branch'])->loadCount(['members', 'resources']);
        } else {
            $plan->setRelation('resources', collect());
            $plan->forceFill([
                'members_count' => 0,
                'resources_count' => 0,
            ]);
        }

        return [
            'plan' => $plan,
            'resourceGroups' => ClubResource::query()
                ->with('branch')
                ->orderBy('branch_id')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (ClubResource $resource) => $resource->branch?->name ?? 'Sem filial'),
        ];
    }

    protected function planAttributes(SavePlanRequest $request, ?Plan $plan = null): array
    {
        $name = $request->validated('name');

        return [
            'name' => $name,
            'slug' => $this->resolveSlug($name, $plan),
            'description' => $request->validated('description'),
            'base_price' => $request->validated('base_price'),
            'dependent_limit' => $request->validated('dependent_limit'),
            'guest_limit_per_reservation' => $request->validated('guest_limit_per_reservation'),
            'free_reservations_per_month' => $request->validated('free_reservations_per_month'),
            'extra_reservation_discount_type' => $request->validated('extra_reservation_discount_type'),
            'extra_reservation_discount_value' => $request->validated('extra_reservation_discount_value'),
            'dependents_inherit_benefits' => $request->boolean('dependents_inherit_benefits'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    protected function resolveSlug(string $name, ?Plan $plan = null): string
    {
        $baseSlug = Str::slug($name) ?: 'plano';
        $slug = $baseSlug;
        $counter = 2;

        while (Plan::query()
            ->where('slug', $slug)
            ->when($plan, fn ($query) => $query->where('id', '!=', $plan->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter += 1;
        }

        return $slug;
    }
}
