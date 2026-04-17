<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportDataController extends Controller
{
    public function index(Request $request, ReportService $reportService)
    {
        abort_unless($request->user()?->isAdminMatrix() || $request->user()?->isAdminBranch(), 403);

        return response()->json(
            $reportService->generate($request->user(), $request->validate([
                'branch_id' => ['nullable', 'exists:branches,id'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
                'status' => ['nullable', 'string'],
                'proposal_origin' => ['nullable', 'in:manual,public'],
                'inventory_category' => ['nullable', 'string', 'max:100'],
            ]))
        );
    }
}
