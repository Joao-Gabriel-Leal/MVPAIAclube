<?php

namespace App\Models;

use App\Enums\FinancialTransactionType;
use Database\Factories\FinancialTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    /** @use HasFactory<FinancialTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'member_id',
        'membership_invoice_id',
        'actor_id',
        'type',
        'amount',
        'occurred_at',
        'description',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => FinancialTransactionType::class,
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
            'meta' => 'array',
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

    public function invoice()
    {
        return $this->belongsTo(MembershipInvoice::class, 'membership_invoice_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
