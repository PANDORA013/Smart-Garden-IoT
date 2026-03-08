<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class DeviceSetting extends Model
{
    use HasFactory;
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
        'last_seen_at',
        'firmware_version',
        'notes',
        'relay_command',
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
        'relay_command' => 'integer',
        'last_seen' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Serialize last_seen in Asia/Jakarta timezone without Z suffix
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get monitoring data for this device
     */
    public function monitorings()
    {
        return $this->hasMany(Monitoring::class, 'device_id', 'device_id');
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
        $now = Carbon::now();
        $this->update(['last_seen' => $now, 'last_seen_at' => $now]);
    }

    /**
     * Cek apakah device online (last_seen dalam 60 detik terakhir)
     * Fallback ke last_seen_at jika last_seen null (kolom lama)
     */
    public function isOnline(): bool
    {
        // Support both column names: last_seen_at (insert()) and last_seen (legacy)
        $lastSeen = $this->last_seen_at ?? $this->last_seen;
        if (!$lastSeen) {
            return false;
        }

        // Device dianggap online jika last seen kurang dari 60 detik yang lalu
        return $lastSeen->diffInSeconds(Carbon::now()) <= 60;
    }

    /**
     * Append formatted last_seen dan is_online untuk API response
     */
    protected $appends = ['last_seen_formatted', 'is_online'];

    public function getLastSeenFormattedAttribute()
    {
        return $this->last_seen ? $this->last_seen->format('Y-m-d H:i:s') : null;
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }
}
