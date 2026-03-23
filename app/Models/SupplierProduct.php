<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    protected $fillable = [
        'supplier_id', 'product_id', 'unit_price', 'last_supplied_at', 'last_quantity',
    ];

    public function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'last_quantity' => 'integer',
            'last_supplied_at' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
