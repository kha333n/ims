<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLedger extends Model
{
    protected $table = 'financial_ledger';

    protected $fillable = [
        'event_type', 'account_id', 'customer_id', 'product_id', 'employee_id',
        'debit', 'credit', 'balance_after', 'description', 'meta', 'event_date',
    ];

    public function casts(): array
    {
        return [
            'debit' => 'integer',
            'credit' => 'integer',
            'balance_after' => 'integer',
            'meta' => 'array',
            'event_date' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Record a ledger entry.
     */
    public static function record(string $type, array $data): self
    {
        return self::create(array_merge(['event_type' => $type, 'event_date' => now()], $data));
    }
}
