<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Monitoring;
use Illuminate\Support\Facades\Log;

/**
 * ModeAIService
 * 
 * Service untuk memproses logika Mode AI (Fuzzy Logic)
 * dengan proteksi keamanan tambahan (watchdog, sensor validation, hysteresis)
 * 
 * Kontrol penuh dari backend Laravel yang berkomunikasi 2 arah dengan Pico W
 */
class ModeAIService
{
    /**
     * Constant
     */
    const MAX_PUMP_DURATION = 300;        // 5 menit (seconds)
    const MIN_ADC_VALUE = 0;              // Minimum ADC value
    const MAX_ADC_VALUE = 4095;           // Maximum ADC value (12-bit)
    const DEFAULT_THRESHOLD_ON = 35;      // Default threshold ON (%)
    const DEFAULT_THRESHOLD_OFF = 65;     // Default threshold OFF (%)
    const DEFAULT_SENSOR_MIN = 4095;      // ADC saat kering (udara)
    const DEFAULT_SENSOR_MAX = 1500;      // ADC saat basah (air)

    /**
     * Proses Mode AI dengan semua proteksi keamanan
     * 
     * @param Device $device
     * @param int $currentAdc - ADC value dari sensor
     * @return array
     */
    public function processAI(Device $device, int $currentAdc): array
    {
        // ===== PROTEKSI 1: Validasi Sensor =====
        if (!$this->validateSensorReading($currentAdc)) {
            Log::warning("[MODE_AI] Invalid sensor reading", [
                'device_id' => $device->device_id,
                'adc_value' => $currentAdc
            ]);
            
            return $this->errorResponse("Sensor reading error");
        }

        // ===== Ambil konfigurasi threshold =====
        $thresholdOn = $device->threshold_on ?? self::DEFAULT_THRESHOLD_ON;
        $thresholdOff = $device->threshold_off ?? self::DEFAULT_THRESHOLD_OFF;

        // ===== Konversi ADC ke Persentase =====
        $soilMoisture = $this->adcToPercentage(
            $currentAdc,
            $device->sensor_min ?? self::DEFAULT_SENSOR_MIN,
            $device->sensor_max ?? self::DEFAULT_SENSOR_MAX
        );

        // ===== PROTEKSI 2: Validasi Persentase Range =====
        if ($soilMoisture < 0 || $soilMoisture > 100) {
            Log::warning("[MODE_AI] Moisture percentage out of range", [
                'device_id' => $device->device_id,
                'moisture' => $soilMoisture
            ]);
            
            return $this->errorResponse("Invalid moisture calculation");
        }

        // ===== LOGIKA FUZZY AI dengan Hysteresis =====
        $shouldPumpOn = false;
        $reason = "";
        $status = "OK";

        if ($soilMoisture < $thresholdOn) {
            // Tanah KERING → Pompa ON
            $shouldPumpOn = true;
            $reason = "DRY: Moisture {$soilMoisture}% < {$thresholdOn}%";

        } elseif ($soilMoisture > $thresholdOff) {
            // Tanah BASAH → Pompa OFF
            $shouldPumpOn = false;
            $reason = "WET: Moisture {$soilMoisture}% > {$thresholdOff}%";

        } else {
            // Tanah NORMAL (antara threshold_on dan threshold_off)
            // Pertahankan kondisi sebelumnya (Hysteresis)
            $lastStatus = (bool) $device->pump_status;
            $shouldPumpOn = $lastStatus;
            $reason = "NORMAL (Hysteresis): Pump " . ($lastStatus ? "ON" : "OFF");
        }

        // ===== PROTEKSI 3: Watchdog Timer =====
        if ($shouldPumpOn) {
            $pumpOnTime = $this->getPumpOnTime($device);

            if ($pumpOnTime > self::MAX_PUMP_DURATION) {
                Log::warning("[MODE_AI] Watchdog triggered", [
                    'device_id' => $device->device_id,
                    'pump_on_time' => $pumpOnTime
                ]);

                $shouldPumpOn = false;
                $reason = "WATCHDOG: Pump ON > " . self::MAX_PUMP_DURATION . "s, FORCE OFF";
                $status = "WATCHDOG";
            }
        }

        // ===== Log Activity =====
        $this->logActivity($device, $currentAdc, $soilMoisture, $thresholdOn, $thresholdOff, $shouldPumpOn, $reason);

        return [
            'should_pump_on' => $shouldPumpOn,
            'reason' => $reason,
            'soil_moisture' => $soilMoisture,
            'status' => $status,
            'adc_value' => $currentAdc,
            'threshold_on' => $thresholdOn,
            'threshold_off' => $thresholdOff
        ];
    }

    /**
     * Konversi ADC value ke soil moisture percentage
     * 
     * Formula: percentage = (sensor_min - raw_adc) / (sensor_min - sensor_max) * 100
     * 
     * @param int $rawAdc
     * @param int $sensorMin - ADC saat kering
     * @param int $sensorMax - ADC saat basah
     * @return int (0-100)
     */
    private function adcToPercentage(int $rawAdc, int $sensorMin, int $sensorMax): int
    {
        // Clamp ke valid range
        $rawAdc = max($sensorMax, min($sensorMin, $rawAdc));

        if ($sensorMin === $sensorMax) {
            return 50; // Safety fallback
        }

        $percentage = (($sensorMin - $rawAdc) * 100) / ($sensorMin - $sensorMax);

        return (int) max(0, min(100, $percentage));
    }

    /**
     * Validasi sensor reading
     * 
     * @param int $adc
     * @return bool
     */
    private function validateSensorReading(int $adc): bool
    {
        // ADC = 0 berarti sensor offline
        if ($adc <= self::MIN_ADC_VALUE) {
            return false;
        }

        // ADC > 4095 invalid (12-bit)
        if ($adc > self::MAX_ADC_VALUE) {
            return false;
        }

        return true;
    }

    /**
     * Hitung berapa lama pompa sudah ON (seconds)
     * 
     * @param Device $device
     * @return int
     */
    private function getPumpOnTime(Device $device): int
    {
        if (!$device->pump_status || !$device->pump_on_at) {
            return 0;
        }

        return (int) now()->diffInSeconds($device->pump_on_at);
    }

    /**
     * Log activity untuk monitoring
     */
    private function logActivity(Device $device, int $adc, int $moisture, int $thresholdOn, int $thresholdOff, bool $shouldPumpOn, string $reason)
    {
        Log::info("[MODE_AI] Processing", [
            'device_id' => $device->device_id,
            'adc_value' => $adc,
            'soil_moisture_percent' => $moisture,
            'threshold_on' => $thresholdOn,
            'threshold_off' => $thresholdOff,
            'should_pump_on' => $shouldPumpOn,
            'reason' => $reason,
            'pump_on_time_seconds' => $this->getPumpOnTime($device)
        ]);
    }

    /**
     * Response error
     */
    private function errorResponse(string $reason): array
    {
        return [
            'should_pump_on' => false,
            'reason' => "ERROR: {$reason}",
            'status' => 'ERROR',
            'soil_moisture' => null
        ];
    }

    /**
     * Validasi mode switch dengan safety check
     * 
     * @param Device $device
     * @param int $newMode
     * @return array
     */
    public function validateModeSwitch(Device $device, int $newMode): array
    {
        // Jika mode sama → skip check
        if ($device->mode === $newMode) {
            return ['allowed' => true];
        }

        // Jika pompa sedang ON → tolak
        if ($device->pump_status && $device->pump_on_at) {
            $pumpOnTime = $this->getPumpOnTime($device);

            // Grace period: izinkan switch jika pump sudah OFF selama > 1 menit
            if ($pumpOnTime > 0) {
                return [
                    'allowed' => false,
                    'message' => '⚠️ Pompa masih aktif. Tunggu sampai OFF dulu sebelum switching mode.',
                    'pump_on_time' => $pumpOnTime
                ];
            }
        }

        return ['allowed' => true];
    }

    /**
     * Validasi konfigurasi threshold
     * 
     * @param int $sensorMin
     * @param int $sensorMax
     * @param int $thresholdOn
     * @param int $thresholdOff
     * @return array
     */
    public function validateConfiguration(int $sensorMin, int $sensorMax, int $thresholdOn, int $thresholdOff): array
    {
        $errors = [];

        // Sensor validation
        if ($sensorMin <= $sensorMax) {
            $errors[] = "Sensor min harus lebih besar dari sensor max";
        }

        // Threshold validation
        if ($thresholdOn >= $thresholdOff) {
            $errors[] = "Threshold ON harus lebih kecil dari OFF";
        }

        if ($thresholdOn < 0 || $thresholdOn > 100) {
            $errors[] = "Threshold ON harus antara 0-100%";
        }

        if ($thresholdOff < 0 || $thresholdOff > 100) {
            $errors[] = "Threshold OFF harus antara 0-100%";
        }

        // Gap validation
        $gap = $thresholdOff - $thresholdOn;
        if ($gap < 10) {
            $errors[] = "Gap antara ON dan OFF minimal 10% (saat ini: {$gap}%)";
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }

    /**
     * Generate command untuk dikirim ke Pico
     * 
     * @param bool $shouldPumpOn
     * @param string $reason
     * @param int|null $moisture
     * @return array
     */
    public function generateCommand(bool $shouldPumpOn, string $reason, int $moisture = null): array
    {
        return [
            'action' => 'pump',
            'state' => $shouldPumpOn ? 'ON' : 'OFF',
            'reason' => $reason,
            'moisture' => $moisture,
            'timestamp' => now()->toIso8601String()
        ];
    }

    /**
     * Process manual mode (weekly loop)
     * 
     * @param Device $device
     * @param int $currentAdc
     * @return array
     */
    public function processManual(Device $device, int $currentAdc): array
    {
        if (!$device->weekly_schedule) {
            return [
                'should_pump_on' => false,
                'reason' => "No weekly schedule configured",
                'status' => 'ERROR'
            ];
        }

        $dayKey = $this->getCurrentDayKey();

        if (!isset($device->weekly_schedule[$dayKey])) {
            return [
                'should_pump_on' => false,
                'reason' => "Day configuration not found",
                'status' => 'ERROR'
            ];
        }

        $dayConfig = $device->weekly_schedule[$dayKey];

        // Check if day is active
        if (!($dayConfig['active'] ?? false)) {
            return [
                'should_pump_on' => false,
                'reason' => "Day not active",
                'status' => 'OK'
            ];
        }

        // Check time ranges
        $currentTime = now();
        $jamPagi = $dayConfig['jam_pagi'] ?? '07:00';
        $jamSore = $dayConfig['jam_sore'] ?? '17:00';

        $isInMorning = $currentTime->format('H:i') >= $jamPagi;
        $isInAfternoon = $currentTime->format('H:i') >= $jamSore;

        if (!$isInMorning && !$isInAfternoon) {
            return [
                'should_pump_on' => false,
                'reason' => "Outside watering time",
                'status' => 'OK'
            ];
        }

        // Check threshold
        $soilMoisture = $this->adcToPercentage(
            $currentAdc,
            $device->sensor_min ?? self::DEFAULT_SENSOR_MIN,
            $device->sensor_max ?? self::DEFAULT_SENSOR_MAX
        );

        $thresholdOn = $dayConfig['threshold_on'] ?? self::DEFAULT_THRESHOLD_ON;
        $shouldPumpOn = $soilMoisture < $thresholdOn;

        return [
            'should_pump_on' => $shouldPumpOn,
            'reason' => "Manual mode - " . ($shouldPumpOn ? "DRY" : "WET"),
            'soil_moisture' => $soilMoisture,
            'status' => 'OK'
        ];
    }

    /**
     * Get current day key (senin, selasa, dst)
     */
    private function getCurrentDayKey(): string
    {
        $dayMap = [
            'Monday' => 'senin',
            'Tuesday' => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday' => 'kamis',
            'Friday' => 'jumat',
            'Saturday' => 'sabtu',
            'Sunday' => 'minggu'
        ];

        $day = now()->format('l');
        return $dayMap[$day] ?? 'senin';
    }

    /**
     * Force pump OFF untuk emergency
     * 
     * @param Device $device
     * @return bool
     */
    public function emergencyStop(Device $device): bool
    {
        return $device->update([
            'pump_status' => false,
            'pump_on_at' => null
        ]);
    }

    /**
     * Get device status untuk UI
     */
    public function getDeviceStatus(Device $device): array
    {
        return [
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'mode' => $device->mode,
            'pump_status' => $device->pump_status,
            'pump_on_time' => $device->pump_status ? $this->getPumpOnTime($device) : 0,
            'last_moisture' => $device->last_moisture,
            'last_update' => $device->updated_at,
            'threshold_on' => $device->threshold_on,
            'threshold_off' => $device->threshold_off,
            'sensor_min' => $device->sensor_min,
            'sensor_max' => $device->sensor_max
        ];
    }
}
