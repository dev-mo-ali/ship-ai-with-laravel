<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentUsage extends Model
{


    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost' => 'decimal:6',
    ];

    public function user(): BelongsTo
    {
        return  $this->belongsTo(User::class);
    }
}
