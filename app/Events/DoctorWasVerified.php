<?php

namespace App\Events;

use App\Models\Doctor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoctorWasVerified
{
    use Dispatchable, SerializesModels;

    public $doctor;

    public function __construct(Doctor $doctor)
    {
        $this->doctor = $doctor;
    }
}
