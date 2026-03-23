<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sale_price', 'purchase_price', 'quantity', 'supplier_id',
        'brand', 'model_number', 'color', 'category', 'image_path', 'notes',
    ];

    public function casts(): array
    {
        return [
            'sale_price' => 'integer',
            'purchase_price' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function accountItems(): HasMany
    {
        return $this->hasMany(AccountItem::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function getFormattedSalePriceAttribute(): string
    {
        return formatMoney($this->sale_price);
    }

    public function getFormattedPurchasePriceAttribute(): string
    {
        return formatMoney($this->purchase_price);
    }
}
