<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkHour extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function workDay(): BelongsTo
    {
        return $this->belongsTo(WorkDay::class);
    }
}
