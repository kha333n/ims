<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'father_name', 'mobile', 'cnic',
        'reference', 'home_address', 'shop_address',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function activeAccounts(): HasMany
    {
        return $this->hasMany(Account::class)->where('status', 'active');
    }

    public function getTotalRemainingAttribute(): int
    {
        return $this->accounts()->where('status', 'active')->sum('remaining_amount');
    }
}
