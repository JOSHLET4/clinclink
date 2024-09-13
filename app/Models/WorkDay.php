<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkDay extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
    public function workHours(): HasMany
    {
        return $this->hasMany(WorkHour::class);
    }
}
