<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileChangeLog extends Model
{
    protected $table = 'file_change_log';

    protected $fillable = [
        'relative_path',
        'action',
        'file_size',
        'file_hash',
        'backed_up',
    ];

    protected function casts(): array
    {
        return [
            'backed_up' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function scopeNotBackedUp($query)
    {
        return $query->where('backed_up', false);
    }
}
