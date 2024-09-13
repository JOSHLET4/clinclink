<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function user(): BelongsTo 
    {
        return $this->belongsTo(DoctorDetail::class, 'doctor_id');
    }
    public function doctorDetailSpecializations(): HasMany
    {
        return $this->hasMany(DoctorDetailSpecialization::class);
    }
}
