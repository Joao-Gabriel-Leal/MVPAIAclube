<?php

namespace App\Http\Controllers;

use App\Enums\DependentStatus;
use App\Http\Requests\SaveDependentRequest;
use App\Http\Requests\UpdateDependentStatusRequest;
use App\Models\Branch;
use App\Models\Dependent;
use App\Models\Member;
use App\Services\DependentService;
use Illuminate\Http\Request;

class DependentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Dependent::class);

        return redirect()->to(route('membros.index').'#dependentes');
    }

    public function create(Request $request)
    {
        $this->authorize('create', Dependent::class);

        $user = $request->user();
        $members = $user->isAdminMatrix()
            ? Member::query()->with('user')->get()
            : ($user->isAdminBranch()
                ? Member::query()->with('user')->where('primary_branch_id', $user->branch_id)->get()
                : collect([$user->member])->filter());
        $selectedMember = $members->firstWhere('id', $request->integer('member_id'))
            ?? ($members->count() === 1 ? $members->first() : null);

        return view('dependents.form', [
            'dependent' => new Dependent([
                'member_id' => $selectedMember?->id,
                'branch_id' => $selectedMember?->primary_branch_id,
            ]),
            'members' => $members,
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'backUrl' => $selectedMember ? $this->memberDependentsUrl($selectedMember) : route('membros.index'),
        ]);
    }

    public function store(SaveDependentRequest $request, DependentService $dependentService)
    {
        $this->authorize('create', Dependent::class);
        $this->guardBranchScope($request);

        $data = $request->validated();

        if ($request->user()->isMember()) {
            $data['member_id'] = $request->user()->member->id;
        }

        $dependent = $dependentService->create($data, $request->user());

        return redirect()->to($this->memberDependentsUrl($dependent->member))->with('status', 'Dependente cadastrado com sucesso.');
    }

    public function show(Dependent $dependent)
    {
        $dependent->load(['user', 'member.user', 'member.plan', 'branch', 'reservations.resource']);
        $this->authorize('view', $dependent);

        return view('dependents.show', [
            'dependent' => $dependent,
            'backUrl' => $this->memberDependentsUrl($dependent->member),
        ]);
    }

    public function edit(Dependent $dependent, Request $request)
    {
        $dependent->load('user');
        $this->authorize('update', $dependent);

        $user = $request->user();

        return view('dependents.form', [
            'dependent' => $dependent,
            'members' => $user->isAdminMatrix()
                ? Member::query()->with('user')->get()
                : ($user->isAdminBranch()
                    ? Member::query()->with('user')->where('primary_branch_id', $user->branch_id)->get()
                    : collect([$user->member])->filter()),
            'branches' => Branch::query()->active()->orderBy('name')->get(),
            'backUrl' => $this->memberDependentsUrl($dependent->member),
        ]);
    }

    public function update(SaveDependentRequest $request, Dependent $dependent, DependentService $dependentService)
    {
        $this->authorize('update', $dependent);
        $this->guardBranchScope($request);
        $dependent = $dependentService->update($dependent, $request->validated(), $request->user());

        return redirect()->to($this->memberDependentsUrl($dependent->member))->with('status', 'Dependente atualizado com sucesso.');
    }

    public function updateStatus(UpdateDependentStatusRequest $request, Dependent $dependent, DependentService $dependentService)
    {
        $this->authorize('approve', $dependent);

        $dependentService->updateStatus(
            $dependent,
            DependentStatus::from($request->validated('status')),
            $request->user()
        );

        return redirect()->to($this->memberDependentsUrl($dependent->member))->with('status', 'Status do dependente atualizado.');
    }

    protected function guardBranchScope(Request $request): void
    {
        if (! $request->user()->isAdminBranch()) {
            return;
        }

        abort_unless((int) $request->input('branch_id', $request->user()->branch_id) === $request->user()->branch_id, 403);
    }

    protected function memberDependentsUrl(Member $member): string
    {
        return route('membros.show', $member).'#dependentes';
    }
}
