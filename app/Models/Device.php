<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'plant_type',
        'status',
        'last_seen',
        'ip_address',
        'hardware_status'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'hardware_status' => 'array'
    ];

    /**
     * Relationship: Has many device settings
     */
    public function settings()
    {
        return $this->hasMany(DeviceSetting::class, 'device_id', 'device_id');
    }

    /**
     * Relationship: Has many monitoring records
     */
    public function monitoringData()
    {
        return $this->hasMany(Monitoring::class, 'device_id', 'device_id');
    }

    /**
     * Check if device is online (online or idle status)
     */
    public function isOnline()
    {
        return in_array($this->status, ['online', 'idle']);
    }
}
