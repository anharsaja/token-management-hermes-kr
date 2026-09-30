<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderBalance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider',
        'balance',
        'currency',
        'last_updated_at',
        'notes',
    ];

    protected $casts = [
        'last_updated_at' => 'date',
        'balance'         => 'decimal:2',
    ];
}
