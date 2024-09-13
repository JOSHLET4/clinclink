<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
    public function userAsDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function medicalExams(): HasMany
    {
        return $this->hasMany(MedicalExam::class);
    }
}
