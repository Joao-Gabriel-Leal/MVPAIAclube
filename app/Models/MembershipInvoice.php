<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\MembershipInvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipInvoice extends Model
{
    /** @use HasFactory<MembershipInvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'member_id',
        'billing_period',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'billing_period' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'status' => InvoiceStatus::class,
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
