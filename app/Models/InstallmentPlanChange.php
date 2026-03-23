<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentPlanChange extends Model
{
    protected $fillable = [
        'account_id', 'old_type', 'old_day', 'old_amount',
        'new_type', 'new_day', 'new_amount', 'changed_at',
    ];

    public function casts(): array
    {
        return [
            'old_amount' => 'integer',
            'new_amount' => 'integer',
            'old_day' => 'integer',
            'new_day' => 'integer',
            'changed_at' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
