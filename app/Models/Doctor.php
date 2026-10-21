<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Model
{
    use HasFactory, Notifiable,HasApiTokens;
    protected $fillable=[
        'user_id',
        'specialty_id',
        'full_name'
        ,'medical_license_number'
        ,'consultation_fee'
        ,'verification_status'
        ,'bio'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function availabilities()
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    public function exceptions()
    {
        return $this->hasMany(DoctorException::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
