<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         User::factory(10)->create();
         Specialty::factory(5)->create()->each(function ($specialty) {
             Doctor::factory(10)->create([
                'specialty_id' => $specialty->id,
            ]);
        });

        Patient::factory(10)->create();

        //ساخت ادمین پیش فرض//
        User::factory()->create([
            'username' => 'Msbb2007',
            'email' => 'mhmdsdrababazadh@gmail.com',
            'phone' =>'09917842608',
            'role' => 'admin',
        ]);
    }
}
