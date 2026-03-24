<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'employee_id',
        'is_active',
        'recovery_key',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'recovery_key',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isSaleMan(): bool
    {
        return $this->role === 'sale_man';
    }

    public function isRecoveryMan(): bool
    {
        return $this->role === 'recovery_man';
    }
}
