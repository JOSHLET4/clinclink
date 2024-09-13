<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResourceRoom extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
