<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function userAsPatient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }
    public function userAsDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id', 'id');
    }
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}
