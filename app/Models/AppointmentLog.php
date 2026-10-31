<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentLog extends Model
{
    protected $fillable = [
        'appointment_id',
        'old_status',
        'new_status',
        'description'
    ];

    public function logs()
    {
        return $this->hasMany(AppointmentLog::class);
    }
}
