<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveInventoryItemRequest;
use App\Http\Requests\StoreInventoryMovementRequest;
use App\Models\Branch;
use App\Models\ClubResource;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Reservation;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use RuntimeException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryItem::class);

        $user = $request->user();
        $selectedBranchId = $user->isAdminBranch()
            ? $user->branch_id
            : ($request->filled('branch_id') ? $request->integer('branch_id') : null);
        $category = $request->string('category')->toString() ?: null;
        $lowStockOnly = $request->boolean('low_stock_only');

        $itemQuery = InventoryItem::query()
            ->with(['branch', 'resource'])
            ->withCount('movements')
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->when($category, fn ($query, $selectedCategory) => $query->where('category', $selectedCategory))
            ->when($lowStockOnly, fn ($query) => $query->whereRaw('current_quantity <= minimum_quantity'))
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name');

        $movementQuery = InventoryMovement::query()
            ->with(['item', 'branch', 'resource', 'reservation.resource', 'actor'])
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->when($category, fn ($query, $selectedCategory) => $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('category', $selectedCategory)))
            ->latest('occurred_at');

        $branches = $user->isAdminMatrix()
            ? Branch::query()->active()->orderBy('name')->get()
            : Branch::query()->active()->whereKey($selectedBranchId)->orderBy('name')->get();
        $resources = ClubResource::query()
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->orderBy('name')
            ->get();
        $reservations = Reservation::query()
            ->with(['resource', 'member.user'])
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->latest('reservation_date')
            ->take(20)
            ->get();
        $items = (clone $itemQuery)->paginate(12)->withQueryString();
        $recentMovements = (clone $movementQuery)->take(12)->get();

        return view('inventory.index', [
            'items' => $items,
            'recentMovements' => $recentMovements,
            'branches' => $branches,
            'resources' => $resources,
            'reservations' => $reservations,
            'categories' => InventoryItem::query()
                ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'filters' => [
                'branch_id' => $selectedBranchId,
                'category' => $category,
                'low_stock_only' => $lowStockOnly,
            ],
            'summary' => [
                'items' => $items->total(),
                'lowStock' => (clone $itemQuery)->whereRaw('current_quantity <= minimum_quantity')->count(),
                'movements' => (clone $movementQuery)->count(),
                'reservationLinked' => (clone $movementQuery)->whereNotNull('reservation_id')->count(),
            ],
            'draftItem' => new InventoryItem([
                'branch_id' => $selectedBranchId,
                'is_active' => true,
            ]),
        ]);
    }

    public function storeItem(SaveInventoryItemRequest $request, InventoryService $inventoryService)
    {
        $this->authorize('create', InventoryItem::class);

        try {
            $inventoryService->createItem($request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('inventory.index', $request->only('branch_id', 'category', 'low_stock_only'))
                ->withErrors(['inventory_item' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.index', $request->only('branch_id', 'category', 'low_stock_only'))
            ->with('status', 'Item de estoque cadastrado com sucesso.');
    }

    public function updateItem(SaveInventoryItemRequest $request, InventoryItem $inventoryItem, InventoryService $inventoryService)
    {
        $this->authorize('update', $inventoryItem);

        try {
            $inventoryService->updateItem($inventoryItem, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('inventory.index', $request->only('branch_id', 'category', 'low_stock_only'))
                ->withErrors(['inventory_item' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.index', $request->only('branch_id', 'category', 'low_stock_only'))
            ->with('status', 'Item de estoque atualizado com sucesso.');
    }

    public function storeMovement(StoreInventoryMovementRequest $request, InventoryItem $inventoryItem, InventoryService $inventoryService)
    {
        $this->authorize('update', $inventoryItem);

        try {
            $inventoryService->recordMovement($inventoryItem, $request->validated(), $request->user());
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('inventory.index', $request->only('branch_id', 'category', 'low_stock_only'))
                ->withErrors(['inventory_movement' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.index', $request->only('branch_id', 'category', 'low_stock_only'))
            ->with('status', 'Movimentacao registrada com sucesso.');
    }
}
