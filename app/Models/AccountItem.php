<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountItem extends Model
{
    protected $fillable = ['account_id', 'product_id', 'quantity', 'unit_price', 'returned'];

    public function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'returned' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }
}
