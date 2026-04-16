<?php

namespace App\Enums;

enum FinancialTransactionType: string
{
    case InvoiceGenerated = 'invoice_generated';
    case PaymentRecorded = 'payment_recorded';
    case StatusChanged = 'status_changed';
    case ManualAdjustment = 'manual_adjustment';

    public function label(): string
    {
        return match ($this) {
            self::InvoiceGenerated => 'Mensalidade gerada',
            self::PaymentRecorded => 'Pagamento baixado',
            self::StatusChanged => 'Status alterado',
            self::ManualAdjustment => 'Ajuste manual',
        };
    }
}
