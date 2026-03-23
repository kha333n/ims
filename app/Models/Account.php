<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'sale_man_id', 'recovery_man_id', 'slip_number',
        'sale_date', 'total_amount', 'advance_amount', 'discount_amount',
        'remaining_amount', 'installment_type', 'installment_day',
        'installment_amount', 'status', 'closed_at', 'discount_slip',
    ];

    public function casts(): array
    {
        return [
            'sale_date' => 'date',
            'closed_at' => 'date',
            'total_amount' => 'integer',
            'advance_amount' => 'integer',
            'discount_amount' => 'integer',
            'remaining_amount' => 'integer',
            'installment_amount' => 'integer',
            'installment_day' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function saleMan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sale_man_id');
    }

    public function recoveryMan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'recovery_man_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccountItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class);
    }

    public function planChanges(): HasMany
    {
        return $this->hasMany(InstallmentPlanChange::class);
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }

    public function getTotalPaidAttribute(): int
    {
        return $this->payments()->sum('amount');
    }
}
