<?php

namespace Database\Factories;

use App\Models\Monitoring;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonitoringFactory extends Factory
{
    protected $model = Monitoring::class;

    public function definition(): array
    {
        return [
            'device_id' => 'PICO_CABAI_01',
            'device_name' => 'Smart Garden Device',
            'temperature' => $this->faker->numberBetween(20, 35),
            'soil_moisture' => $this->faker->numberBetween(20, 80),
            'raw_adc' => $this->faker->numberBetween(1000, 3000),
            'relay_status' => $this->faker->boolean(),
            'ip_address' => $this->faker->ipv4(),
            'hardware_status' => [
                'dht22' => true,
                'soil_sensor' => true,
                'relay' => true,
                'servo' => false,
                'lcd' => true,
            ],
        ];
    }
}
