<?php

namespace App\Http\Requests;

use App\Enums\InventoryMovementReason;
use App\Enums\InventoryMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'movement_type' => ['required', Rule::enum(InventoryMovementType::class)],
            'reason' => ['required', Rule::enum(InventoryMovementReason::class)],
            'quantity' => ['required', 'numeric', 'not_in:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'club_resource_id' => ['nullable', 'exists:club_resources,id'],
            'reservation_id' => ['nullable', 'exists:reservations,id'],
            'occurred_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
