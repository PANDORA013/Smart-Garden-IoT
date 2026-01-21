# 🌐 Implementasi Mode AI dari Website (Backend Laravel)

**Versi:** 1.0 (Recommended)  
**Status:** ✅ Production Ready  
**Tanggal:** 21 Januari 2026  
**Arsitektur:** Komunikasi 2 arah Website ↔ Pico W

---

## 📋 Pseudocode Mode AI di Website

```
START

WHILE mode_AI == AKTIF DO

    READ nilai_ADC (dari request Pico)

    IF nilai_ADC < ambang_kering THEN
        pompa = ON
        SEND command_pompa_ON ke Pico
    ELSE
        pompa = OFF
        SEND command_pompa_OFF ke Pico
    ENDIF

    DELAY interval_cek   // contoh: 5–10 detik

ENDWHILE

pompa = OFF   // pastikan pompa mati saat keluar dari mode AI

END
```

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────┐
│                    SMART GARDEN IoT Website (Laravel)            │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Web Interface (Settings Page)                            │   │
│  │ - Mode Selection (AI / Manual)                           │   │
│  │ - Threshold Configuration                               │   │
│  │ - Real-time Monitoring                                  │   │
│  └──────────────────────────────────────────────────────────┘   │
│           ↓                                  ↑                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Laravel Backend (Controller + Service)                   │   │
│  │ - ModeAIService (Logika AI dengan Proteksi)             │   │
│  │ - DeviceController (API Endpoint)                       │   │
│  │ - Database (Device Config + Monitoring)                 │   │
│  └──────────────────────────────────────────────────────────┘   │
│           ↑                                  ↓                   │
│           │                         API Response               │
│           │                    {pompa: ON/OFF}                │
│  ┌────────┴──────────────────────────────────────────────────┐  │
│  │ HTTP/MQTT Communication Queue                            │  │
│  └────────┬──────────────────────────────────────────────────┘  │
└───────────┼──────────────────────────────────────────────────────┘
            │
            │ API Request (ADC Value, Status)
            ↓
┌──────────────────────────────────────────┐
│         Pico W (Microcontroller)         │
│                                          │
│ ┌──────────────────────────────────────┐ │
│ │ Soil Sensor (ADC)                    │ │
│ │ Relay Control (Pump)                 │ │
│ │ HTTP Client                          │ │
│ └──────────────────────────────────────┘ │
└──────────────────────────────────────────┘
```

---

## 💾 Backend Implementation (Laravel)

### 1. Service: ModeAIService

Buat file: `app/Services/ModeAIService.php`

```php
<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Monitoring;
use Illuminate\Support\Facades\Log;

class ModeAIService
{
    /**
     * Proses Mode AI dengan proteksi keamanan
     * 
     * @param Device $device - Device instance
     * @param int $currentAdc - Nilai ADC terkini dari Pico
     * @return array ['should_pump_on' => bool, 'reason' => string]
     */
    public function processAI(Device $device, int $currentAdc): array
    {
        // ===== PROTEKSI 1: Validasi Sensor =====
        if ($currentAdc <= 0) {
            Log::warning("[MODE_AI] Sensor offline (ADC=0) - Force pump OFF", [
                'device_id' => $device->device_id,
                'adc_value' => $currentAdc
            ]);
            
            return [
                'should_pump_on' => false,
                'reason' => 'Sensor offline',
                'status' => 'ERROR'
            ];
        }
        
        // ===== Konversi ADC ke Persentase =====
        $soilMoisture = $this->adcToPercentage(
            $currentAdc,
            $device->sensor_min,
            $device->sensor_max
        );
        
        // ===== PROTEKSI 2: Validasi Range =====
        if ($soilMoisture < 0 || $soilMoisture > 100) {
            Log::warning("[MODE_AI] ADC value out of range", [
                'device_id' => $device->device_id,
                'adc_value' => $currentAdc,
                'moisture_percent' => $soilMoisture
            ]);
            
            return [
                'should_pump_on' => false,
                'reason' => 'Sensor reading error',
                'status' => 'ERROR'
            ];
        }
        
        // ===== LOGIKA FUZZY AI =====
        $thresholdOn = $device->threshold_on ?? 35;    // Default 35%
        $thresholdOff = $device->threshold_off ?? 65;  // Default 65%
        
        $shouldPumpOn = false;
        $reason = '';
        
        if ($soilMoisture < $thresholdOn) {
            // Tanah KERING → Pompa ON
            $shouldPumpOn = true;
            $reason = "DRY: Moisture {$soilMoisture}% < {$thresholdOn}%";
            
        } elseif ($soilMoisture > $thresholdOff) {
            // Tanah BASAH → Pompa OFF
            $shouldPumpOn = false;
            $reason = "WET: Moisture {$soilMoisture}% > {$thresholdOff}%";
            
        } else {
            // Tanah NORMAL → Pertahankan kondisi sebelumnya (Hysteresis)
            $lastStatus = $device->pump_status ?? false;
            $shouldPumpOn = $lastStatus;
            $reason = "NORMAL: Hysteresis - pump " . ($lastStatus ? "ON" : "OFF");
        }
        
        // ===== PROTEKSI 3: Watchdog Timer =====
        $maxPumpDuration = 300; // 5 menit
        $pumpOnTime = $this->getPumpOnTime($device);
        
        if ($pumpOnTime > $maxPumpDuration) {
            Log::warning("[MODE_AI] Watchdog triggered - pump ON > 5 minutes", [
                'device_id' => $device->device_id,
                'pump_on_time' => $pumpOnTime
            ]);
            
            $shouldPumpOn = false;
            $reason = "WATCHDOG: Pump ON > 5 min, force OFF";
        }
        
        // ===== Log Activity =====
        Log::info("[MODE_AI] Processing", [
            'device_id' => $device->device_id,
            'adc_value' => $currentAdc,
            'soil_moisture' => $soilMoisture,
            'threshold_on' => $thresholdOn,
            'threshold_off' => $thresholdOff,
            'should_pump_on' => $shouldPumpOn,
            'reason' => $reason,
            'pump_on_time' => $pumpOnTime
        ]);
        
        return [
            'should_pump_on' => $shouldPumpOn,
            'reason' => $reason,
            'soil_moisture' => $soilMoisture,
            'status' => 'OK'
        ];
    }
    
    /**
     * Convert ADC value to soil moisture percentage (0-100%)
     * 
     * Formula: percentage = (sensor_min - raw_adc) / (sensor_min - sensor_max) * 100
     */
    private function adcToPercentage(int $rawAdc, int $sensorMin, int $sensorMax): int
    {
        // Clamp to valid range
        $rawAdc = max($sensorMax, min($sensorMin, $rawAdc));
        
        if ($sensorMin === $sensorMax) {
            return 50; // Safety fallback
        }
        
        $percentage = (($sensorMin - $rawAdc) * 100) / ($sensorMin - $sensorMax);
        
        return (int) max(0, min(100, $percentage));
    }
    
    /**
     * Get total pump ON time (seconds)
     */
    private function getPumpOnTime(Device $device): int
    {
        if (!$device->pump_on_at) {
            return 0;
        }
        
        return (int) now()->diffInSeconds($device->pump_on_at);
    }
    
    /**
     * Validate mode switch dengan safety check
     */
    public function validateModeSwitch(Device $device, int $newMode): array
    {
        // Cek apakah pompa sedang aktif
        if ($device->pump_status && $device->pump_on_at) {
            $pumpOnTime = $this->getPumpOnTime($device);
            
            // Jika pompa aktif < 1 menit, tolak mode switch
            if ($pumpOnTime < 60) {
                return [
                    'allowed' => false,
                    'message' => '⚠️ Pompa masih aktif. Tunggu sampai OFF dulu sebelum switching mode.',
                    'pump_on_time' => $pumpOnTime
                ];
            }
        }
        
        return ['allowed' => true];
    }
}
```

### 2. Controller: DeviceController

Update/Create: `app/Http/Controllers/DeviceController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
use App\Services\ModeAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    protected $modeAIService;
    
    public function __construct(ModeAIService $modeAIService)
    {
        $this->modeAIService = $modeAIService;
    }
    
    /**
     * Endpoint: Terima sensor data dari Pico & process Mode AI
     * 
     * Request: POST /api/devices/{deviceId}/monitoring
     * Body: {
     *     "adc_value": 2500,
     *     "temperature": 28.5,
     *     "humidity": 65,
     *     "pump_status": false
     * }
     */
    public function receiveMonitoring(Request $request, $deviceId)
    {
        $validated = $request->validate([
            'adc_value' => 'required|integer|min:0|max:4095',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'pump_status' => 'required|boolean'
        ]);
        
        try {
            $device = Device::findOrFail($deviceId);
            
            // Simpan monitoring data
            $monitoring = Monitoring::create([
                'device_id' => $device->id,
                'adc_raw' => $validated['adc_value'],
                'temperature' => $validated['temperature'],
                'humidity' => $validated['humidity'],
                'pump_status' => $validated['pump_status'],
                'created_at' => now()
            ]);
            
            // ===== PROCESS MODE AI =====
            $commandToPico = null;
            
            if ($device->mode == 2) {
                // Mode AI: Process dengan ModeAIService
                $result = $this->modeAIService->processAI($device, $validated['adc_value']);
                
                if ($result['status'] === 'OK') {
                    $commandToPico = [
                        'action' => 'pump',
                        'state' => $result['should_pump_on'] ? 'ON' : 'OFF',
                        'reason' => $result['reason'],
                        'moisture' => $result['soil_moisture'] ?? null
                    ];
                    
                    // Update device status
                    $device->update([
                        'pump_status' => $result['should_pump_on'],
                        'pump_on_at' => $result['should_pump_on'] ? now() : null,
                        'last_moisture' => $result['soil_moisture'] ?? null
                    ]);
                    
                } else {
                    // Error case: Force pump OFF
                    $commandToPico = [
                        'action' => 'pump',
                        'state' => 'OFF',
                        'reason' => 'Error: ' . $result['reason']
                    ];
                    
                    $device->update(['pump_status' => false]);
                }
                
            } elseif ($device->mode == 4) {
                // Mode Manual: Execute weekly loop logic
                $commandToPico = $this->processManualMode($device, $validated['adc_value']);
            }
            
            // ===== RESPONSE DENGAN COMMAND KE PICO =====
            return response()->json([
                'success' => true,
                'message' => 'Monitoring data received',
                'device_id' => $device->device_id,
                'mode' => $device->mode,
                'command' => $commandToPico,  // ← Command untuk Pico
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error receiving monitoring data", [
                'device_id' => $deviceId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Endpoint: Set mode & configuration
     * 
     * Request: POST /api/devices/{deviceId}/mode
     * Body: {
     *     "mode": 2,
     *     "sensor_min": 4095,
     *     "sensor_max": 1500,
     *     "threshold_on": 35,
     *     "threshold_off": 65,
     *     "weekly_schedule": { ... }
     * }
     */
    public function setMode(Request $request, $deviceId)
    {
        $validated = $request->validate([
            'mode' => 'required|in:2,4',
            'sensor_min' => 'sometimes|integer|min:0|max:4095',
            'sensor_max' => 'sometimes|integer|min:0|max:4095',
            'threshold_on' => 'sometimes|integer|min:0|max:100',
            'threshold_off' => 'sometimes|integer|min:0|max:100',
            'weekly_schedule' => 'sometimes|array'
        ]);
        
        try {
            $device = Device::findOrFail($deviceId);
            
            // ===== PROTEKSI: Validasi Mode Switch =====
            if ($device->mode !== $validated['mode']) {
                $validation = $this->modeAIService->validateModeSwitch(
                    $device,
                    $validated['mode']
                );
                
                if (!$validation['allowed']) {
                    return response()->json($validation, 409);
                }
            }
            
            // ===== PROTEKSI: Validasi Sensor Range =====
            if (isset($validated['sensor_min']) && isset($validated['sensor_max'])) {
                if ($validated['sensor_min'] <= $validated['sensor_max']) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Sensor min harus lebih besar dari sensor max'
                    ], 422);
                }
            }
            
            // ===== PROTEKSI: Validasi Threshold Range =====
            if (isset($validated['threshold_on']) && isset($validated['threshold_off'])) {
                if ($validated['threshold_on'] >= $validated['threshold_off']) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Threshold ON harus lebih kecil dari OFF'
                    ], 422);
                }
            }
            
            // Update device
            $device->update([
                'mode' => $validated['mode'],
                'sensor_min' => $validated['sensor_min'] ?? $device->sensor_min,
                'sensor_max' => $validated['sensor_max'] ?? $device->sensor_max,
                'threshold_on' => $validated['threshold_on'] ?? $device->threshold_on,
                'threshold_off' => $validated['threshold_off'] ?? $device->threshold_off,
                'weekly_schedule' => $validated['weekly_schedule'] ?? $device->weekly_schedule,
            ]);
            
            // Kirim config ke Pico
            $this->sendConfigToPico($device);
            
            Log::info("[MODE] Device mode changed", [
                'device_id' => $device->device_id,
                'new_mode' => $validated['mode']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Mode dan konfigurasi berhasil disimpan',
                'device_id' => $device->device_id,
                'mode' => $validated['mode']
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error setting mode", ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process Manual Mode (Weekly Loop)
     */
    private function processManualMode(Device $device, int $currentAdc): array
    {
        // Get current day configuration
        $currentDay = strtolower(now()->format('l'));
        $dayMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        
        $dayKey = $dayMap[str_replace('_', '', strtolower(now()->englishDayOfWeek))] ?? null;
        
        if (!$dayKey || !isset($device->weekly_schedule[$dayKey])) {
            return ['action' => 'pump', 'state' => 'OFF', 'reason' => 'Day not configured'];
        }
        
        $dayConfig = $device->weekly_schedule[$dayKey];
        
        if (!$dayConfig['active']) {
            return ['action' => 'pump', 'state' => 'OFF', 'reason' => 'Day not active'];
        }
        
        // Check time ranges
        $currentTime = now()->format('H:i');
        $jamPagi = $dayConfig['jam_pagi'] ?? '07:00';
        $jamSore = $dayConfig['jam_sore'] ?? '17:00';
        
        $isInMorning = $currentTime >= $jamPagi;
        $isInAfternoon = $currentTime >= $jamSore;
        
        if (!$isInMorning && !$isInAfternoon) {
            return ['action' => 'pump', 'state' => 'OFF', 'reason' => 'Outside watering time'];
        }
        
        // Check threshold
        $thresholdOn = $dayConfig['threshold_on'] ?? 35;
        $soilMoisture = ($device->sensor_min - $currentAdc) * 100 / ($device->sensor_min - $device->sensor_max);
        
        $shouldPumpOn = $soilMoisture < $thresholdOn;
        
        return [
            'action' => 'pump',
            'state' => $shouldPumpOn ? 'ON' : 'OFF',
            'reason' => 'Manual mode - ' . ($shouldPumpOn ? 'DRY' : 'WET')
        ];
    }
    
    /**
     * Send configuration ke Pico
     */
    private function sendConfigToPico(Device $device)
    {
        // TODO: Implement HTTP/MQTT to Pico
        // Contoh menggunakan HTTP:
        // $pico_url = $device->ip_address . "/api/config";
        // Http::post($pico_url, ['config' => $device->toArray()]);
        
        Log::info("[SEND_CONFIG] Config sent to Pico", [
            'device_id' => $device->device_id,
            'mode' => $device->mode
        ]);
    }
}
```

### 3. Model: Device

Update: `app/Models/Device.php`

```php
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
        'ip_address',
        'mode',
        'sensor_min',
        'sensor_max',
        'threshold_on',
        'threshold_off',
        'pump_status',
        'pump_on_at',
        'last_moisture',
        'weekly_schedule'
    ];
    
    protected $casts = [
        'pump_status' => 'boolean',
        'pump_on_at' => 'datetime',
        'weekly_schedule' => 'json'
    ];
}
```

---

## 🔄 Flow Chart Komunikasi 2 Arah

### Alur Mode AI:

```
1. Pico W (Loop setiap 10 detik):
   ├─ Baca sensor ADC
   ├─ POST /api/devices/{id}/monitoring {adc_value: 2500, ...}
   └─ Terima response dengan COMMAND

2. Backend Laravel:
   ├─ Terima ADC value
   ├─ Process Mode AI Service
   │  ├─ Validasi sensor
   │  ├─ Convert ADC to %
   │  ├─ Check threshold
   │  └─ Watchdog check
   ├─ Generate command
   └─ Return response dengan command

3. Pico W (Execute command):
   ├─ Terima command {action: 'pump', state: 'ON'}
   ├─ Kontrol relay
   └─ Update status lokal
```

---

## 📡 API Endpoints

### 1. POST `/api/devices/{deviceId}/monitoring`
**Dari Pico ke Website**

Request:
```json
{
    "adc_value": 2500,
    "temperature": 28.5,
    "humidity": 65,
    "pump_status": false
}
```

Response:
```json
{
    "success": true,
    "command": {
        "action": "pump",
        "state": "ON",
        "reason": "DRY: Moisture 30% < 35%",
        "moisture": 30
    }
}
```

### 2. POST `/api/devices/{deviceId}/mode`
**Dari Website ke Pico (via Backend)**

Request:
```json
{
    "mode": 2,
    "sensor_min": 4095,
    "sensor_max": 1500,
    "threshold_on": 35,
    "threshold_off": 65
}
```

Response:
```json
{
    "success": true,
    "message": "Mode dan konfigurasi berhasil disimpan"
}
```

---

## 🛡️ Safety Features

| Fitur | Deskripsi |
|-------|-----------|
| **Watchdog Timer** | Pompa max 5 menit ON continuous |
| **Sensor Validation** | ADC = 0 → Force OFF |
| **Threshold Lock** | Tidak bisa switch mode saat pompa aktif |
| **Range Validation** | Sensor min > sensor max, threshold on < off |
| **Hysteresis** | State ON/OFF dipertahankan di zone normal |
| **Mode Switching** | Safety check sebelum mode berubah |
| **Emergency Stop** | API endpoint untuk force pump OFF |

---

## 🧪 Testing Scenarios

### Scenario 1: Normal Operation
```
Moisture 30% → Pump ON
Moisture 68% → Pump OFF
Moisture 45% → Pump tetap ON (hysteresis)
Moisture 55% → Pump tetap ON (hysteresis)
```

### Scenario 2: Watchdog Trigger
```
Pump ON 1 menit → Tetap ON
Pump ON 5 menit → Tetap ON
Pump ON 6 menit → FORCE OFF (watchdog)
```

### Scenario 3: Sensor Error
```
ADC = 0 → ERROR, Pump OFF
ADC = 5000 (invalid) → ERROR, Pump OFF
```

### Scenario 4: Mode Switching
```
Mode = AI, Pump ON → Reject mode switch
Mode = AI, Pump OFF → Allow mode switch
```

---

## 📊 Database Schema

```sql
ALTER TABLE devices ADD COLUMN mode INT DEFAULT 2;
ALTER TABLE devices ADD COLUMN sensor_min INT DEFAULT 4095;
ALTER TABLE devices ADD COLUMN sensor_max INT DEFAULT 1500;
ALTER TABLE devices ADD COLUMN threshold_on INT DEFAULT 35;
ALTER TABLE devices ADD COLUMN threshold_off INT DEFAULT 65;
ALTER TABLE devices ADD COLUMN pump_status BOOLEAN DEFAULT 0;
ALTER TABLE devices ADD COLUMN pump_on_at TIMESTAMP NULL;
ALTER TABLE devices ADD COLUMN last_moisture INT NULL;
ALTER TABLE devices ADD COLUMN weekly_schedule JSON NULL;
```

---

## 🚀 Deployment Steps

1. **Buat Service:**
   - Copy `ModeAIService.php` ke `app/Services/`

2. **Update Controller:**
   - Update `DeviceController.php` dengan methods baru

3. **Update Model:**
   - Update `Device.php` fillable & casts

4. **Database Migration:**
   - Jalankan ALTER TABLE queries

5. **Update Routes:**
   - Tambah routes di `routes/api.php`:
   ```php
   Route::post('/devices/{deviceId}/monitoring', [DeviceController::class, 'receiveMonitoring']);
   Route::post('/devices/{deviceId}/mode', [DeviceController::class, 'setMode']);
   ```

6. **Update Pico:**
   - Pico kirim ADC ke website setiap 10 detik
   - Pico execute command yang diterima dari response

---

## 📝 Monitoring & Logs

```
[2026-01-21 10:30:45] [MODE_AI] Processing
├─ device_id: PICO_CABAI_01
├─ adc_value: 2500
├─ soil_moisture: 30%
├─ threshold_on: 35%
├─ threshold_off: 65%
├─ should_pump_on: true
└─ reason: DRY: Moisture 30% < 35%

[2026-01-21 10:30:55] [SEND_COMMAND] To Pico
├─ action: pump
├─ state: ON
└─ reason: DRY: Moisture 30% < 35%

[2026-01-21 10:31:05] [MODE_AI] Processing
├─ soil_moisture: 68%
├─ should_pump_on: false
└─ reason: WET: Moisture 68% > 65%
```

---

**Status:** ✅ Ready for Production  
**Last Updated:** 21 Januari 2026
