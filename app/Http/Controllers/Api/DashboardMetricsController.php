<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardMetricsController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        return response()->json($dashboardService->summary($request->user()));
    }
}
