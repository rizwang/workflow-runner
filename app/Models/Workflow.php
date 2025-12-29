<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class)->orderBy('order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }
}
