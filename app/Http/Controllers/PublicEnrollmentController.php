<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnrollmentRequest;
use App\Models\Branch;
use App\Models\ClubSetting;
use App\Models\Plan;
use App\Services\EnrollmentService;

class PublicEnrollmentController extends Controller
{
    public function create(Branch $branch)
    {
        return view('public.enrollment', [
            'branch' => $branch,
            'clubSetting' => ClubSetting::current(),
            'plans' => Plan::query()->active()->orderBy('id')->get(),
        ]);
    }

    public function store(StoreEnrollmentRequest $request, Branch $branch, EnrollmentService $enrollmentService)
    {
        $plan = Plan::query()->active()->findOrFail($request->validated('plan_id'));
        $enrollmentService->enroll($branch, $plan, $request->validated());

        return redirect()->route('login')->with('status', 'Cadastro enviado com sucesso. Aguarde a aprovacao da administracao.');
    }
}
