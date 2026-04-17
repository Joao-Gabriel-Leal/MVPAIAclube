<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BenefitController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing([
            'member.plan.resources.branch',
            'member.primaryBranch',
            'dependent.member.plan.resources.branch',
            'dependent.member.primaryBranch',
            'dependent.branch',
        ]);

        $member = $user->activeMember();
        abort_unless($member, 404);

        $member->loadMissing(['plan.resources.branch', 'primaryBranch', 'additionalBranches']);
        $plan = $member->plan;
        $resources = $plan?->resources?->sortBy('name')->values() ?? collect();

        return view('benefits.index', [
            'viewer' => $user,
            'member' => $member,
            'plan' => $plan,
            'resources' => $resources,
            'highlights' => [
                'Filial principal' => $member->primaryBranch?->name ?? '-',
                'Dependentes inclusos' => $plan?->dependent_limit ?? 0,
                'Convidados por reserva' => $plan?->guest_limit_per_reservation ?? 0,
                'Reservas gratis por mes' => $plan?->free_reservations_per_month ?? 0,
            ],
        ]);
    }
}
