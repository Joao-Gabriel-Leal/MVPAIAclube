<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, ReportService $reportService)
    {
        abort_unless($request->user()?->isAdminMatrix() || $request->user()?->isAdminBranch(), 403);

        return view('reports.index', [
            'reportData' => $reportService->generate($request->user(), $request->validated()),
        ]);
    }
}
