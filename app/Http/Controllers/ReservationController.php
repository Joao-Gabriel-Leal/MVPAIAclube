<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Models\ClubResource;
use App\Models\Dependent;
use App\Models\Member;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use RuntimeException;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Reservation::class);

        $query = Reservation::query()->with(['resource', 'member.user', 'branch', 'reserver']);
        $user = $request->user();

        if ($user->isAdminBranch()) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isMember()) {
            $query->where('member_id', $user->member?->id);
        } elseif ($user->isDependent()) {
            $query
                ->where('reserver_type', $user->dependent?->getMorphClass())
                ->where('reserver_id', $user->dependent?->id);
        }

        return view('reservations.index', [
            'reservations' => $query->latest('reservation_date')->paginate(15),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Reservation::class);

        $user = $request->user();
        $selectedBranchId = $user->isAdminBranch()
            ? $user->branch_id
            : ($request->filled('branch_id') ? $request->integer('branch_id') : null);

        return view('reservations.form', [
            'resources' => ClubResource::query()
                ->with('branch')
                ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
                ->orderBy('name')
                ->get(),
            'members' => $user->isAdminMatrix()
                ? Member::query()->with('user')
                    ->when($selectedBranchId, fn ($query) => $query->where('primary_branch_id', $selectedBranchId))
                    ->get()
                : ($user->isAdminBranch()
                    ? Member::query()->with('user')->where('primary_branch_id', $user->branch_id)->get()
                    : collect([$user->member])->filter()),
            'dependents' => $user->isAdminMatrix()
                ? Dependent::query()->with('user')
                    ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
                    ->get()
                : ($user->isAdminBranch()
                    ? Dependent::query()->with('user')->where('branch_id', $user->branch_id)->get()
                    : ($user->isMember()
                        ? Dependent::query()->with('user')->where('member_id', $user->member?->id)->get()
                        : collect([$user->dependent])->filter())),
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function store(StoreReservationRequest $request, ReservationService $reservationService)
    {
        $this->authorize('create', Reservation::class);

        try {
            $reservationService->createReservation($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['reservation' => $exception->getMessage()]);
        }

        return redirect()->route('reservas.index')->with('status', 'Reserva criada com sucesso.');
    }

    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation, ReservationService $reservationService)
    {
        $this->authorize('updateStatus', $reservation);

        $reservationService->updateStatus(
            $reservation,
            ReservationStatus::from($request->validated('status')),
            $request->user(),
            $request->validated('notes')
        );

        return redirect()->route('reservas.index')->with('status', 'Status da reserva atualizado.');
    }
}
