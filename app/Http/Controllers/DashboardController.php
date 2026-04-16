<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        if ($request->user()->isAdminMatrix()) {
            return redirect()->route('filiais.index');
        }

        if ($request->user()->isAdminBranch()) {
            $branch = Branch::query()->findOrFail($request->user()->branch_id);

            return redirect()->route('filiais.show', $branch);
        }

        return view('dashboard', [
            'summary' => $dashboardService->summary($request->user()),
        ]);
    }
}
