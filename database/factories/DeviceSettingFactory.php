<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceSetting>
 */
class DeviceSettingFactory extends Factory
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
            'plant_type' => $this->faker->randomElement(['Cabai', 'Tomat', 'Bayam']),
            'mode' => $this->faker->randomElement([2, 4]),
            'sensor_min' => 4095,
            'sensor_max' => 1500,
            'relay_command' => false,
        ];
    }
}
