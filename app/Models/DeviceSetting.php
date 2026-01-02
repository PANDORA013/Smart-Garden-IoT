<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'device_name',
        'plant_type',
        'mode',
        'sensor_min',
        'sensor_max',
        'batas_siram',
        'batas_stop',
        'jam_pagi',
        'jam_sore',
        'durasi_siram',
        'is_active',
        'last_seen',
        'firmware_version',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mode' => 'integer',
        'sensor_min' => 'integer',
        'sensor_max' => 'integer',
        'batas_siram' => 'integer',
        'batas_stop' => 'integer',
        'durasi_siram' => 'integer',
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
    ];

    /**
     * Get monitoring data for this device
     */
    public function monitorings()
    {
        return $this->hasMany(Monitoring::class, 'device_name', 'device_id');
    }

    /**
     * Default settings untuk tanaman cabai
     */
    public static function cabaiDefaults(): array
    {
        return [
            'plant_type' => 'cabai',
            'sensor_min' => 4095,
            'sensor_max' => 1500,
            'batas_siram' => 40,
            'batas_stop' => 70,
        ];
    }

    /**
     * Default settings untuk tanaman tomat
     */
    public static function tomatDefaults(): array
    {
        return [
            'plant_type' => 'tomat',
            'sensor_min' => 4095,
            'sensor_max' => 1500,
            'batas_siram' => 60,
            'batas_stop' => 80,
        ];
    }

    /**
     * Update last_seen timestamp
     */
    public function updateLastSeen()
    {
        $this->update(['last_seen' => now()]);
    }
}
