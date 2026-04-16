<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Http\Requests\SaveMemberRequest;
use App\Http\Requests\UpdateMemberStatusRequest;
use App\Models\Branch;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Plan;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Member::class);

        $user = $request->user();
        $search = trim((string) $request->string('q'));
        $digitsSearch = $this->normalizeDigits($search);
        $status = $request->string('status')->toString() ?: null;
        $selectedBranchId = $user->isAdminBranch()
            ? $user->branch_id
            : ($user->isMember()
                ? $user->member?->primary_branch_id
                : ($user->isDependent()
                    ? $user->dependent?->branch_id
                    : ($request->filled('branch_id') ? $request->integer('branch_id') : null)));

        $memberQuery = Member::query()
            ->with(['user', 'plan', 'primaryBranch', 'additionalBranches'])
            ->withCount('dependents');

        if ($user->isAdminBranch()) {
            $memberQuery->where(function ($builder) use ($user) {
                $builder
                    ->where('primary_branch_id', $user->branch_id)
                    ->orWhereHas('additionalBranches', fn ($branchQuery) => $branchQuery->where('branches.id', $user->branch_id));
            });
        } elseif ($user->isMember()) {
            $memberQuery->where('user_id', $user->id);
        } elseif ($user->isDependent()) {
            $memberQuery->whereKey($user->dependent?->member_id);
        }

        if ($status) {
            $memberQuery->where('status', $status);
        }

        if ($selectedBranchId && $user->isAdminMatrix()) {
            $memberQuery->where('primary_branch_id', $selectedBranchId);
        }

        if ($search !== '') {
            $memberQuery->where(function ($builder) use ($search, $digitsSearch) {
                $builder
                    ->whereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->when($digitsSearch !== '', fn ($query) => $query
                            ->orWhere('cpf', 'like', "%{$digitsSearch}%")
                            ->orWhere('phone', 'like', "%{$digitsSearch}%")))
                    ->orWhereHas('plan', fn ($planQuery) => $planQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('primaryBranch', fn ($branchQuery) => $branchQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $dependentQuery = Dependent::query()
            ->with(['user', 'member.user', 'member.plan', 'branch']);

        if ($user->isAdminBranch()) {
            $dependentQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isMember()) {
            $dependentQuery->where('member_id', $user->member?->id);
        } elseif ($user->isDependent()) {
            $dependentQuery->whereKey($user->dependent?->id);
        }

        if ($selectedBranchId && $user->isAdminMatrix()) {
            $dependentQuery->where('branch_id', $selectedBranchId);
        }

        if ($status) {
            $dependentQuery->where('status', $status);
        }

        if ($search !== '') {
            $dependentQuery->where(function ($builder) use ($search, $digitsSearch) {
                $builder
                    ->whereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->when($digitsSearch !== '', fn ($query) => $query
                            ->orWhere('cpf', 'like', "%{$digitsSearch}%")
                            ->orWhere('phone', 'like', "%{$digitsSearch}%")))
                    ->orWhereHas('member.user', fn ($holderQuery) => $holderQuery
                        ->where('name', 'like', "%{$search}%")
                        ->when($digitsSearch !== '', fn ($query) => $query
                            ->orWhere('cpf', 'like', "%{$digitsSearch}%")
                            ->orWhere('phone', 'like', "%{$digitsSearch}%")))
                    ->orWhereHas('branch', fn ($branchQuery) => $branchQuery->where('name', 'like', "%{$search}%"))
                    ->orWhere('relationship', 'like', "%{$search}%");
            });
        }

        $branches = $user->isAdminMatrix()
            ? Branch::query()->active()->orderBy('name')->get()
            : Branch::query()
                ->active()
                ->whereKey($selectedBranchId ?? $user->branch_id)
                ->orderBy('name')
                ->get();

        return view('members.index', [
            'members' => $memberQuery->latest()->paginate(12)->withQueryString(),
            'dependents' => $dependentQuery->latest()->paginate(12, ['*'], 'dependents_page')->withQueryString(),
            'branches' => $branches,
            'statuses' => MembershipStatus::cases(),
            'search' => $search,
            'selectedBranch' => $selectedBranchId ? Branch::query()->find($selectedBranchId) : null,
            'summary' => [
                'members' => (clone $memberQuery)->count(),
                'dependents' => (clone $dependentQuery)->count(),
                'branches' => $selectedBranchId ? 1 : max($branches->count(), 1),
                'hasFilters' => $search !== '' || $status !== null || $request->filled('branch_id'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Member::class);

        return view('members.form', [
            'member' => new Member(),
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'plans' => Plan::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(SaveMemberRequest $request, MemberService $memberService)
    {
        $this->authorize('create', Member::class);
        $this->guardBranchScope($request);

        $memberService->create([
            ...$request->validated(),
            'status' => request()->user()->isAdminMatrix() ? ($request->validated('status') ?? MembershipStatus::Pending->value) : MembershipStatus::Pending->value,
        ], $request->user());

        return redirect()->route('membros.index')->with('status', 'Associado cadastrado com sucesso.');
    }

    public function show(Member $member)
    {
        $member->load(['user', 'plan', 'primaryBranch', 'additionalBranches', 'dependents.user', 'dependents.branch', 'invoices', 'reservations.resource']);
        $this->authorize('view', $member);

        return view('members.show', [
            'member' => $member,
        ]);
    }

    public function edit(Member $member)
    {
        $member->load(['user', 'additionalBranches']);
        $this->authorize('update', $member);

        return view('members.form', [
            'member' => $member,
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'plans' => Plan::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(SaveMemberRequest $request, Member $member, MemberService $memberService)
    {
        $this->authorize('update', $member);
        $this->guardBranchScope($request);
        $memberService->update($member, $request->validated(), $request->user());

        return redirect()->route('membros.show', $member)->with('status', 'Associado atualizado com sucesso.');
    }

    public function updateStatus(UpdateMemberStatusRequest $request, Member $member, MemberService $memberService)
    {
        $this->authorize('approve', $member);

        $memberService->updateStatus(
            $member,
            MembershipStatus::from($request->validated('status')),
            $request->user(),
            $request->validated('notes')
        );

        return redirect()->route('membros.show', $member)->with('status', 'Status do associado atualizado.');
    }

    protected function guardBranchScope(Request $request): void
    {
        if (! $request->user()->isAdminBranch()) {
            return;
        }

        abort_unless($request->integer('primary_branch_id') === $request->user()->branch_id, 403);

        $additionalBranches = collect($request->input('additional_branch_ids', []))->filter();
        abort_unless($additionalBranches->isEmpty() || $additionalBranches->every(fn ($branchId) => (int) $branchId === $request->user()->branch_id), 403);
    }

    protected function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
