<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'date_of_birth',
        'profile_picture_uri'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'state'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // uno a muchos destiono
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    // uno a muchos origen
    public function moduleUserPermissions(): HasMany
    {
        return $this->hasMany(ModuleUserPermission::class);
    }
    // uno a uno origen
    public function doctorDetail(): HasOne
    {
        return $this->hasOne(DoctorDetail::class, 'id', 'doctor_id');
    }

    public function appointmentsAsPatient(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id', 'patient_id');
    }
    public function appointmentsAsDoctor(): HasMany
    {
        return $this->hasMany(Appointment::class, 'id', 'doctor_id');
    }


    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'id', 'doctor_id');
    }


    public function medicalRecordsAsPatient(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'id', 'patient_id');
    }
    public function medicalRecordsAsDoctor(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'id', 'doctor_id');
    }

    public function consultationsAsDoctor(): HasMany
    {
        return $this->hasMany(Consultation::class, 'id', 'doctor_id');
    }
}
