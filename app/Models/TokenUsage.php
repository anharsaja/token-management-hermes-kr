<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TokenUsage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agent_id',
        'model',
        'input_tokens',
        'output_tokens',
        'cost',
        'used_at',
        'notes',
    ];

    protected $casts = [
        'used_at'       => 'date',
        'input_tokens'  => 'integer',
        'output_tokens' => 'integer',
        'cost'          => 'decimal:6',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }
}
