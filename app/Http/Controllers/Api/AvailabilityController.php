<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClubResource;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    public function index(Request $request, ClubResource $clubResource, ReservationService $reservationService)
    {
        $this->authorize('view', $clubResource);

        return response()->json([
            'resource' => $clubResource->only(['id', 'name']),
            'date' => $request->query('date'),
            'slots' => $reservationService
                ->availability($clubResource->load('schedules'), Carbon::parse($request->query('date', now()->toDateString())))
                ->values(),
        ]);
    }

    public function month(Request $request, ClubResource $clubResource, ReservationService $reservationService)
    {
        $this->authorize('view', $clubResource);

        $month = Carbon::createFromFormat('Y-m', $request->query('month', now()->format('Y-m')))->startOfMonth();

        return response()->json([
            'resource' => $clubResource->only(['id', 'name']),
            'month' => $month->format('Y-m'),
            'days' => $reservationService
                ->monthlyAvailability($clubResource->load('schedules'), $month)
                ->values(),
        ]);
    }
}
