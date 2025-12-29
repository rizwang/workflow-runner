<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunLog extends Model
{
    protected $fillable = [
        'run_id',
        'step_id',
        'level',
        'message',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }
}
