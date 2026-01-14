<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_id',
        'connected_devices',
        'device_name',
        'ip_address',
        'temperature',
        'soil_moisture',
        'status_pompa',
        'relay_status',
        'raw_adc',
        'hardware_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'temperature' => 'float',
        'soil_moisture' => 'float',
        'relay_status' => 'boolean',
        'raw_adc' => 'integer',
        'hardware_status' => 'array',
    ];

    public function isOnline()
    {
        if (!$this->updated_at) {
            return false;
        }
        return $this->updated_at->diffInSeconds(now()) < 30;
    }
}
