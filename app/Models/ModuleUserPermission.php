<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleUserPermission extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
