<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
    public function appointmentStatus(): BelongsTo
    {
        return $this->belongsTo(AppointmentStatus::class);
    }
    public function userAsPatient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function userAsDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}
