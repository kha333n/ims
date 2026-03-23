<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransfer extends Model
{
    protected $fillable = [
        'account_id', 'from_recovery_man_id', 'to_recovery_man_id', 'transfer_date', 'notes',
    ];

    public function casts(): array
    {
        return ['transfer_date' => 'date'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function fromRecoveryMan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_recovery_man_id');
    }

    public function toRecoveryMan(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_recovery_man_id');
    }
}
