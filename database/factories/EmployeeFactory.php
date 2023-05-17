<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone' => $this->faker->e164PhoneNumber,  // método e164PhoneNumber()   Lo proporciona la clase 'Faker'.   Genera un nº aleatorio en formato E.164
            'department_id' => $this->faker->numberBetween(1,6)
        ];
    }
}
