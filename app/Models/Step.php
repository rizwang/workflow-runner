<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Step extends Model
{
    protected $fillable = [
        'workflow_id',
        'type',
        'config',
        'order',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function runLogs(): HasMany
    {
        return $this->hasMany(RunLog::class);
    }
}
