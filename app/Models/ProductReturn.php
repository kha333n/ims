<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'account_id', 'account_item_id', 'quantity',
        'returning_amount', 'return_date', 'reason', 'inventory_action',
    ];

    public function casts(): array
    {
        return [
            'quantity' => 'integer',
            'returning_amount' => 'integer',
            'return_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function accountItem(): BelongsTo
    {
        return $this->belongsTo(AccountItem::class);
    }
}
