<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'account_id', 'amount', 'transaction_type', 'payment_date', 'collected_by', 'remarks',
    ];

    public function casts(): array
    {
        return [
            'amount' => 'integer',
            'payment_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'collected_by');
    }
}
