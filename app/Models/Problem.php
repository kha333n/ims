<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Problem extends Model
{
    protected $fillable = [
        'account_id', 'manager', 'checker', 'branch', 'problem_text',
        'previous_promise_date', 'new_commitment_date', 'action_taken', 'closed',
    ];

    public function casts(): array
    {
        return [
            'previous_promise_date' => 'date',
            'new_commitment_date' => 'date',
            'closed' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
