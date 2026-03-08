<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => 'PICO_' . $this->faker->unique()->word(),
            'device_name' => $this->faker->word() . ' Device',
            'plant_type' => $this->faker->randomElement(['Cabai', 'Tomat', 'Bayam', 'Kangkung']),
            'status' => $this->faker->randomElement(['online', 'idle', 'offline']),
            'ip_address' => $this->faker->ipv4(),
            'last_seen' => $this->faker->dateTime(),
        ];
    }
}
