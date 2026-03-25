<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = ['product_id', 'supplier_id', 'quantity', 'remaining_qty', 'unit_cost', 'purchase_date', 'notes', 'batch_number'];

    public function casts(): array
    {
        return [
            'quantity' => 'integer',
            'remaining_qty' => 'integer',
            'unit_cost' => 'integer',
            'purchase_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
