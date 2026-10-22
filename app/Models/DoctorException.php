<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorException extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'is_off',
        'start_time',
        'end_time',
        'slot_duration_minutes'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
