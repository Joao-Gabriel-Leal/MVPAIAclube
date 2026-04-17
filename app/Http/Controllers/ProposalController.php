<?php

namespace App\Http\Controllers;

use App\Services\ProposalService;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index(Request $request, ProposalService $proposalService)
    {
        abort_unless($request->user()?->isAdminMatrix() || $request->user()?->isAdminBranch(), 403);

        return view('proposals.index', $proposalService->paginatedFor($request->user(), $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'type' => ['nullable', 'in:member,dependent'],
            'origin' => ['nullable', 'in:manual,public'],
            'age' => ['nullable', 'in:recent,attention,stale'],
        ])));
    }
}
