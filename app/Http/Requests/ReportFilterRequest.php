<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'report' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', 'max:50'],
            'proposal_origin' => ['nullable', 'in:manual,public'],
            'inventory_category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
