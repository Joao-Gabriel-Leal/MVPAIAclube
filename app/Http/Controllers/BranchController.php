<?php

namespace App\Http\Controllers;

use App\Enums\BranchType;
use App\Http\Requests\SaveBranchRequest;
use App\Models\Branch;
use App\Services\BillingService;
use App\Services\BranchHubService;
use App\Support\MaskFormatter;
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
                'pending_members' => Branch::query()
                    ->withCount([
                        'members as pending_members_count' => fn ($query) => $query->where('status', 'pending'),
                    ])
                    ->get()
                    ->sum('pending_members_count'),
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
                'estoque' => 'Estoque',
                'financeiro' => 'Financeiro',
                'relatorios' => 'Relatorios',
            ]),
            ...$branchHubService->build($branch, $request->user(), $request->only(['start_date', 'end_date', 'status', 'proposal_origin', 'inventory_category', 'billing_period'])),
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

        Branch::query()->create($this->branchAttributes($request));

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

        $branch->update($this->branchAttributes($request, $branch));

        return redirect()->route('filiais.index')->with('status', 'Filial atualizada com sucesso.');
    }

    protected function resolveTab(?string $tab): string
    {
        $allowedTabs = ['resumo', 'planos', 'recursos', 'reservas', 'estoque', 'financeiro', 'relatorios'];

        return in_array($tab, $allowedTabs, true) ? $tab : 'resumo';
    }

    protected function branchAttributes(SaveBranchRequest $request, ?Branch $branch = null): array
    {
        return [
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('slug')),
            'type' => $request->validated('type'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'monthly_fee_default' => $request->validated('monthly_fee_default'),
            'is_active' => (bool) $request->boolean('is_active', true),
            'settings' => $this->mergePublicBranchSettings($branch, (array) $request->validated('settings', [])),
        ];
    }

    protected function mergePublicBranchSettings(?Branch $branch, array $settings): array
    {
        $existing = (array) ($branch?->settings ?? []);
        $managedSettings = [
            'city' => trim((string) ($settings['city'] ?? '')),
            'summary' => trim((string) ($settings['summary'] ?? '')),
            'public_phone' => MaskFormatter::digits((string) ($settings['public_phone'] ?? '')),
            'public_whatsapp' => MaskFormatter::digits((string) ($settings['public_whatsapp'] ?? '')),
            'public_hours' => trim((string) ($settings['public_hours'] ?? '')),
        ];

        foreach ($managedSettings as $key => $value) {
            if ($value !== null && $value !== '') {
                $existing[$key] = $value;

                continue;
            }

            unset($existing[$key]);
        }

        return $existing;
    }
}
