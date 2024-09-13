<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialization extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function doctorDetailSpecializations(): HasMany
    {
        return $this->hasMany(DoctorDetailSpecialization::class);
    }
}
