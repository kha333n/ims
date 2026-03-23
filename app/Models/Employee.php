<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'phone', 'cnic', 'address',
        'commission_percent', 'salary', 'area', 'rank',
    ];

    public function casts(): array
    {
        return [
            'commission_percent' => 'integer',
            'salary' => 'integer',
        ];
    }

    public function scopeSaleMen(Builder $query): Builder
    {
        return $query->where('type', 'sale_man');
    }

    public function scopeRecoveryMen(Builder $query): Builder
    {
        return $query->where('type', 'recovery_man');
    }

    public function accountsAsSaleMan(): HasMany
    {
        return $this->hasMany(Account::class, 'sale_man_id');
    }

    public function accountsAsRecoveryMan(): HasMany
    {
        return $this->hasMany(Account::class, 'recovery_man_id');
    }

    public function paymentsCollected(): HasMany
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }
}
