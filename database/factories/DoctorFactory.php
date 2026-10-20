<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'doctor']),
            'full_name' => $this->faker->name(),
            'specialty_id' => Specialty::factory(),
            'medical_license_number' =>  fake()->unique()->numerify('#####'),
            'bio' => fake()->paragraph(),
            'consultation_fee' => fake()->numberBetween(200000, 1000000), // مبلغ ویزیت به عنوان عدد//
        ];
    }
}
