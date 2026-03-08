<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Insert data dari Raspberry Pi Pico W / ESP32
     * Endpoint: POST /api/monitoring/insert
     * 
     * **2-WAY COMMUNICATION:**
     * 1. Terima data sensor dari Pico
     * 2. Simpan ke database
     * 3. AMBIL konfigurasi dari device_settings
     * 4. RETURN konfigurasi ke Pico (Kalibrasi + Mode + Threshold)
     * 
     * Expected JSON from Pico Gateway:
     * {
     *   "device_id": "PICO_CABAI_01",
     *   "temperature": 28.5,
     *   "humidity": 64.0,
     *   "soil_moisture": 35.5,
     *   "raw_adc": 3200,
     *   "relay_status": true,
     *   "ip_address": "192.168.1.105"
     * }
     * 
     * Response (Config for Pico):
     * {
     *   "success": true,
     *   "config": {
     *     "mode": 1,
     *     "adc_min": 4095,
     *     "adc_max": 1500,
     *     "batas_kering": 40,
     *     "batas_basah": 70,
     *     "jam_pagi": "07:00",
     *     "jam_sore": "17:00",
     *     "durasi_siram": 5
     *   }
     * }
     */
    public function insert(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:100',
            'temperature' => 'nullable|numeric|min:-50|max:100',
            'soil_moisture' => 'nullable|numeric|min:0|max:100', // Opsional (dihitung di backend)
            'raw_adc' => 'nullable|integer|min:0|max:4095', // 12-bit ADC from Pico
            'raw_adc_raw' => 'nullable|integer|min:0|max:4095', // Debug: unfiltered ADC
            'relay_status' => 'nullable|boolean',
            'device_name' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'hardware_status' => 'nullable|array',
            'command_executing' => 'nullable|boolean',  // Status eksekusi command
            'last_command' => 'nullable|string|max:10',  // Command terakhir (ON/OFF)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. AMBIL/BUAT KONFIGURASI (Auto-Provisioning) - Untuk kalkulasi soil_moisture
        $cacheKey = 'device_setting_' . $request->device_id;
        
        $setting = cache()->remember($cacheKey, 60, function() use ($request) {
            return \App\Models\DeviceSetting::firstOrCreate(
                ['device_id' => $request->device_id],
                [
                    'device_name' => $request->device_name ?? $request->device_id,
                    'mode' => 1,
                    'sensor_min' => 4095,   // Default: Capacitive sensor (kering = ADC tinggi)
                    'sensor_max' => 1500,   // Default: Capacitive sensor (basah = ADC rendah)
                    'batas_siram' => 20,
                    'batas_stop' => 30,
                ]
            );
        });

        // 2. HITUNG SOIL MOISTURE dari RAW ADC (Backend-side calculation)
        $soilMoisture = null;
        $rawAdc = $request->raw_adc;
        
        if ($rawAdc !== null) {
            $adcKering = $setting->sensor_min;  // ADC saat kering
            $adcBasah = $setting->sensor_max;   // ADC saat basah
            
            // Deteksi jenis sensor dan hitung moisture
            if ($adcKering > $adcBasah) {
                // CAPACITIVE: Kering=Tinggi, Basah=Rendah
                $rawClamped = max($adcBasah, min($adcKering, $rawAdc));
                $dryness = ($rawClamped - $adcBasah) / ($adcKering - $adcBasah) * 100;
                $soilMoisture = round(100 - $dryness, 1);
            } else {
                // RESISTIVE: Kering=Rendah, Basah=Tinggi  
                $rawClamped = max($adcKering, min($adcBasah, $rawAdc));
                $wetness = ($rawClamped - $adcKering) / ($adcBasah - $adcKering) * 100;
                $soilMoisture = round($wetness, 1);
            }
            
            // Batasi 0-100%
            $soilMoisture = max(0, min(100, $soilMoisture));
        }

        // 3. SIMPAN DATA SENSOR (gunakan soil_moisture yang dihitung)
        $data = [
            'device_id' => $request->device_id,
            'device_name' => $request->device_name ?? $request->device_id,
            'temperature' => $request->temperature,
            'soil_moisture' => $soilMoisture,  // Dari kalkulasi backend
            'raw_adc' => $rawAdc,
            'relay_status' => $request->relay_status ?? false,
            'status_pompa' => $request->relay_status ? 'Hidup' : 'Mati',
            'ip_address' => $request->ip_address,
            'hardware_status' => $request->hardware_status ?? null,
        ];

        $monitoring = Monitoring::create($data);
        
        // LOG STATUS EKSEKUSI COMMAND (untuk debugging)
        if ($request->has('command_executing')) {
            if ($request->command_executing) {
                Log::info("⚙️ COMMAND EXECUTING - Device: {$request->device_id}, Command: {$request->last_command}");
            } else if ($request->last_command) {
                Log::info("✅ COMMAND EXECUTED - Device: {$request->device_id}, Command: {$request->last_command}");
            }
        }
        
        // ===== AUTO CALIBRATION SYSTEM =====
        // Sistem otomatis mendeteksi jenis sensor (capacitive/resistive) dan kalibrasi range
        $needsCalibration = ($setting->sensor_min == 4095 && $setting->sensor_max == 1500);
        
        if ($needsCalibration && $rawAdc) {
            // Mode: Auto-learning dari sample data
            // Ambil 30 sample terakhir untuk analisis (lebih banyak = lebih akurat)
            $recentSamples = Monitoring::where('device_id', $request->device_id)
                ->whereNotNull('raw_adc')
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->pluck('raw_adc');
            
            // Jika sudah ada minimal 30 sample, kalkulasi min/max
            if ($recentSamples->count() >= 30) {
                $minADC = $recentSamples->min();
                $maxADC = $recentSamples->max();
                $avgADC = $recentSamples->avg();
                $range = $maxADC - $minADC;
                
                // Validasi: ADC range harus masuk akal (minimal 100 untuk 12-bit ADC)
                if ($range >= 100) {
                    // Deteksi jenis sensor berdasarkan range ADC
                    // Capacitive: Range 500-3000 (dry=high, wet=low)
                    // Resistive: Range 100-2000 (dry=low, wet=high)
                    
                    $sensorType = 'unknown';
                    
                    if ($avgADC > 1500 && $range > 500) {
                        // Kemungkinan CAPACITIVE (ADC rata-rata tinggi)
                        $sensorType = 'capacitive';
                        $setting->update([
                            'sensor_min' => $maxADC,  // Kering = ADC tinggi
                            'sensor_max' => $minADC,  // Basah = ADC rendah
                        ]);
                    } else if ($avgADC < 1500 && $range > 100) {
                        // Kemungkinan RESISTIVE (ADC rata-rata rendah)
                        $sensorType = 'resistive';
                        $setting->update([
                            'sensor_min' => $minADC,  // Kering = ADC rendah
                            'sensor_max' => $maxADC,  // Basah = ADC tinggi
                        ]);
                    }
                    
                    if ($sensorType !== 'unknown') {
                        cache()->forget($cacheKey);
                        $setting->refresh(); // Re-sync local model with updated DB values
                        $needsCalibration = false; // Mark as calibrated for remainder of this request
                        Log::info("🎯 AUTO CALIBRATION SUCCESS - Device: {$request->device_id}, Type: {$sensorType}, Range: {$range}, Avg: " . round($avgADC));
                    } else {
                        Log::warning("⚠️ AUTO CALIBRATION UNCERTAIN - Device: {$request->device_id}, Avg: {$avgADC}, Range: {$range}");
                    }
                } else {
                    Log::warning("⚠️ AUTO CALIBRATION SKIPPED - Device: {$request->device_id}, Range too small: {$range}");
                }
            } else {
                Log::info("📊 Collecting calibration samples - Device: {$request->device_id}, Current: {$recentSamples->count()}/30");
            }
        }

        // Update last_seen dan last_seen_at setiap 30 detik (skip jika baru saja update)
        if (!$setting->last_seen || $setting->last_seen->diffInSeconds(Carbon::now()) > 30) {
            $setting->update([
                'last_seen' => Carbon::now(),
                'last_seen_at' => Carbon::now(),
            ]);
            cache()->forget($cacheKey); // Refresh cache setelah update
        }

        // 3. KIRIM KONFIGURASI BALIK KE PICO (2-Way Communication)
        $response = [
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $monitoring,
            
            // === CONFIG UNTUK PICO (Otak Cerdas) ===
            'config' => [
                'mode' => $setting->mode,
                
                // Kalibrasi ADC (Pico gunakan ini untuk konversi ADC → %)
                'adc_min' => $setting->sensor_min,
                'adc_max' => $setting->sensor_max,
                
                // Status kalibrasi
                'is_calibrated' => !$needsCalibration,
                'calibration_status' => $needsCalibration ? 'collecting_samples' : 'ready',
                
                // Threshold Mode 1 (Basic)
                'batas_kering' => $setting->batas_siram,
                'batas_basah' => $setting->batas_stop,
                
                // Schedule Mode 3
                'jam_pagi' => substr($setting->jam_pagi, 0, 5), // "07:00"
                'jam_sore' => substr($setting->jam_sore, 0, 5), // "17:00"
                'durasi_siram' => $setting->durasi_siram,
            ]
        ];
        
        // 4. CEK ADA RELAY COMMAND DARI WEB (Manual Control via Toggle)
        // PENTING: Manual command PRIORITAS TERTINGGI - Override mode auto
        
        if ($setting->relay_command !== null) {
            $response['relay_command'] = $setting->relay_command;
            Log::info("📤 SENDING COMMAND TO PICO - Device: {$request->device_id}, Command: " . ($setting->relay_command ? 'ON' : 'OFF'));
            
            // Reset HANYA jika relay_status dari Pico W MATCH dengan command
            $currentRelayStatus = (int)$request->relay_status;
            $commandValue = (int)$setting->relay_command;
            
            if ($currentRelayStatus === $commandValue) {
                // Command sudah dijalankan - reset ke null
                $setting->update(['relay_command' => null]);
                cache()->forget($cacheKey);
                Log::info("✅ COMMAND COMPLETED - Device: {$request->device_id}, Relay now: " . ($currentRelayStatus ? 'ON' : 'OFF'));
            } else {
                Log::info("⏳ WAITING FOR EXECUTION - Device: {$request->device_id}, Expected: {$commandValue}, Current: {$currentRelayStatus}");
            }
        }
        // 5. JIKA TIDAK ADA MANUAL COMMAND, JALANKAN LOGIKA AUTO BERDASARKAN MODE
        else {
            // ===== SAFETY CHECK: SKIP AUTO LOGIC JIKA BELUM KALIBRASI =====
            if ($needsCalibration) {
                Log::info("⏸️ AUTO LOGIC SKIPPED - Device {$request->device_id} still calibrating (collecting samples)");
                // Tidak kirim relay_command, biarkan relay maintain status sekarang
            } else {
                // Sistem sudah terkalibrasi, jalankan logika auto normal
                $soilMoisture = (int)$request->soil_moisture;
                $currentRelayStatus = (int)$request->relay_status;
                $autoCommand = null;
            
            // === MODE 1: BASIC THRESHOLD (Kelembaban) ===
            if ($setting->mode == 1) {
                // Logika Hysteresis: Hindari relay flicker
                // - Jika kelembaban < batas_siram (misal 20%) -> NYALAKAN pompa
                // - Jika kelembaban >= batas_stop (misal 30%) -> MATIKAN pompa
                // - Jika di antara 20-30% -> PERTAHANKAN status sekarang
                
                if ($soilMoisture < $setting->batas_siram) {
                    // Tanah terlalu kering -> Nyalakan pompa
                    $autoCommand = 1;
                    Log::info("🌱 MODE 1 AUTO: Soil {$soilMoisture}% < {$setting->batas_siram}% -> Pump ON");
                } elseif ($soilMoisture >= $setting->batas_stop) {
                    // Tanah sudah cukup basah -> Matikan pompa
                    $autoCommand = 0;
                    Log::info("💧 MODE 1 AUTO: Soil {$soilMoisture}% >= {$setting->batas_stop}% -> Pump OFF");
                } else {
                    // Di zona hysteresis -> Pertahankan status sekarang
                    $autoCommand = $currentRelayStatus;
                    Log::info("⏸️  MODE 1 AUTO: Soil {$soilMoisture}% (Hysteresis zone) -> Keep current status: " . ($currentRelayStatus ? 'ON' : 'OFF'));
                }
            }
            
            // === MODE 2: FUZZY LOGIC ===
            elseif ($setting->mode == 2) {
                // Logika Fuzzy berdasarkan 3 kategori kelembaban
                // - Kering (0-30%): Durasi siram LAMA
                // - Sedang (30-60%): Durasi siram SEDANG
                // - Basah (60-100%): TIDAK perlu siram
                
                if ($soilMoisture < 30) {
                    // Sangat kering -> Siram lama
                    $autoCommand = 1;
                    Log::info("🔥 MODE 2 FUZZY: Soil {$soilMoisture}% (DRY) -> Pump ON (Long)");
                } elseif ($soilMoisture < 60) {
                    // Sedang -> Siram singkat
                    $autoCommand = 1;
                    Log::info("🌤️  MODE 2 FUZZY: Soil {$soilMoisture}% (MEDIUM) -> Pump ON (Short)");
                } else {
                    // Basah -> Tidak perlu siram
                    $autoCommand = 0;
                    Log::info("💦 MODE 2 FUZZY: Soil {$soilMoisture}% (WET) -> Pump OFF");
                }
            }
            
            // === MODE 3: SCHEDULE (Jadwal Pagi & Sore) ===
            elseif ($setting->mode == 3) {
                // Mode schedule: Siram sesuai jadwal jam_pagi dan jam_sore
                // Durasi siram sesuai durasi_siram (dalam menit)
                // Window: ±5 menit dari jam yang dijadwalkan
                
                $nowCarbon = Carbon::now();
                $jamPagiCarbon = Carbon::createFromTimeString($setting->jam_pagi);
                $jamSoreCarbon = Carbon::createFromTimeString($setting->jam_sore);
                $withinPagi = abs($nowCarbon->diffInMinutes($jamPagiCarbon, false)) <= 5;
                $withinSore = abs($nowCarbon->diffInMinutes($jamSoreCarbon, false)) <= 5;
                
                // Siram jika dalam window jadwal DAN tanah < 50%
                if (($withinPagi || $withinSore) && $soilMoisture < 50) {
                    $autoCommand = 1;
                    $schedLabel = $withinPagi ? 'pagi' : 'sore';
                    Log::info("⏰ MODE 3 SCHEDULE: Within {$schedLabel} window, Soil {$soilMoisture}% -> Pump ON");
                } else {
                    $autoCommand = 0;
                    Log::info("⏰ MODE 3 SCHEDULE: Outside schedule or soil OK -> Pump OFF");
                }
            }
            
            // Jika ada auto command dan BERBEDA dengan status sekarang -> Kirim command
            if ($autoCommand !== null) {
                Log::info("🔍 Auto decision: autoCommand={$autoCommand}, currentRelayStatus={$currentRelayStatus}, mode={$setting->mode}");
                
                if ($autoCommand !== $currentRelayStatus) {
                    $response['relay_command'] = $autoCommand;
                    Log::info("🤖 AUTO COMMAND sent: " . ($autoCommand ? 'ON' : 'OFF') . " - Mode: {$setting->mode}");
                } else {
                    Log::info("✅ Relay already in desired state - No command needed");
                }
            }
            } // End of calibration check else block
        }
        
        // DEBUG: Log final response structure before return
        Log::info("📦 FINAL RESPONSE: " . json_encode($response));
        
        return response()->json($response, 201);
    }

    /**
     * Ambil data terbaru untuk dashboard
     * Endpoint: GET /api/monitoring/latest?device_id=PICO_CABAI_01
     */
    public function latest(Request $request)
    {
        $deviceId = $request->input('device_id');

        $query = Monitoring::latest();
        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }

        $latest = $query->first();

        if (!$latest) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data',
                'data' => [
                    'temperature' => 0,
                    'soil_moisture' => 0,
                    'relay_status' => false,
                    'status_pompa' => 'Mati',
                    'device_name' => null,
                    'ip_address' => null,
                    'is_online' => false,
                ]
            ], 200);
        }

        // Tambahkan status online ke response
        $data = $latest->toArray();
        $data['is_online'] = $latest->isOnline();

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Ambil history data (untuk chart/grafik)
     * Endpoint: GET /api/monitoring/history?limit=50
     */
    public function history(Request $request)
    {
        $limit = $request->input('limit', 50);
        $deviceId = $request->input('device_id');
        // Include device_id in cache key to prevent cross-device cache contamination
        $cacheKey = 'history_' . ($deviceId ?? 'all') . '_' . $limit;
        
        // Cache for 3 seconds to reduce database load
        $history = cache()->remember($cacheKey, 3, function() use ($limit, $deviceId) {
            $query = Monitoring::latest()->take($limit);
            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }
            return $query->get()->reverse()->values();
        });

        return response()->json([
            'success' => true,
            'count' => $history->count(),
            'data' => $history
        ], 200)->header('Cache-Control', 'public, max-age=2');
    }

    /**
     * Hapus data lama (cleanup)
     * Endpoint: DELETE /api/monitoring/cleanup?days=7
     */
    public function cleanup(Request $request)
    {
        $days = (int)$request->input('days', 7);
        
        if ($days === 0) {
            // Delete ALL monitoring data
            $deleted = Monitoring::count();
            Monitoring::query()->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus semua data monitoring ({$deleted} record)",
                'deleted_count' => $deleted
            ], 200);
        }

        $deleted = Monitoring::where('created_at', '<', Carbon::now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$deleted} data lama (> {$days} hari)",
            'deleted_count' => $deleted
        ], 200);
    }

    /**
     * Toggle relay status (untuk kontrol manual dari dashboard)
     * Endpoint: POST /api/monitoring/relay/toggle
     * Body: { "status": true }
     */
    public function toggleRelay(Request $request)
    {
        try {
            // Validasi input menggunakan Request class
            $validated = $request->validate([
                'status' => 'nullable|boolean',
                'relay_status' => 'nullable|boolean',
                'device_id' => 'nullable|string|max:100',
            ]);

            // Support both field names: 'status' (legacy) and 'relay_status' (new)
            $relayStatus = $validated['relay_status'] ?? $validated['status'] ?? null;
            if ($relayStatus === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field status atau relay_status diperlukan',
                ], 422);
            }

            $deviceId = $validated['device_id'] ?? 'PICO_CABAI_01';
            
            // Log request
            \App\Services\ApiLoggerService::logRequest($request, 'toggleRelay', [
                'device_id' => $deviceId,
                'status' => $relayStatus
            ]);

            // Check device exists
            $device = \App\Models\Device::where('device_id', $deviceId)->first();
            if (!$device) {
                \App\Services\ApiLoggerService::logError(
                    'toggleRelay',
                    new \Exception("Device not found: {$deviceId}"),
                    ['device_id' => $deviceId],
                    404
                );
                
                return response()->json([
                    'success' => false,
                    'message' => 'Device tidak ditemukan'
                ], 404);
            }

            // Check device is online — check BOTH Device.status AND DeviceSetting.last_seen_at
            // because the heartbeat from insert() only updates DeviceSetting, not Device.status.
            $recentlySeen = \App\Models\DeviceSetting::where('device_id', $deviceId)
                ->where('last_seen_at', '>=', Carbon::now()->subMinutes(2))
                ->exists();

            if (!$recentlySeen && $device->status !== 'online' && $device->status !== 'idle') {
                \App\Services\ApiLoggerService::logError(
                    'toggleRelay',
                    new \Exception("Device offline: {$deviceId}"),
                    ['device_id' => $deviceId, 'device_status' => $device->status],
                    400
                );
                
                return response()->json([
                    'success' => false,
                    'message' => 'Device sedang offline, tidak dapat mengontrol relay'
                ], 400);
            }

            // Update relay command
            $setting = \App\Models\DeviceSetting::where('device_id', $deviceId)->first();
            
            if (!$setting) {
                \App\Services\ApiLoggerService::logError(
                    'toggleRelay',
                    new \Exception("DeviceSetting not found: {$deviceId}"),
                    ['device_id' => $deviceId],
                    404
                );
                
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi device tidak ditemukan'
                ], 404);
            }

            // Update relay command atomically: store as integer (1=ON, 0=OFF, null=no command)
            // Use a DB transaction with pessimistic locking to prevent race condition
            // from simultaneous toggle requests overwriting each other.
            \Illuminate\Support\Facades\DB::transaction(function () use ($deviceId, $relayStatus) {
                \App\Models\DeviceSetting::where('device_id', $deviceId)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->update(['relay_command' => $relayStatus ? 1 : 0]);
            });
            cache()->forget('device_setting_' . $deviceId);
            
            // Log device control action
            \App\Services\ApiLoggerService::logDeviceControl(
                'relay_toggle',
                $device->id,
                ['status' => $relayStatus, 'device_id' => $deviceId]
            );
            
            \App\Services\ApiLoggerService::logSuccess('toggleRelay', [
                'device_id' => $deviceId,
                'relay_status' => $relayStatus
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Relay command berhasil dikirim',
                'relay_command' => $relayStatus
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \App\Services\ApiLoggerService::logValidationError('toggleRelay', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \App\Services\ApiLoggerService::logError('toggleRelay', $e);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengontrol relay'
            ], 500);
        }
    }

    /**
     * Get statistics untuk dashboard (Multi-Device Support)
     * Endpoint: GET /api/monitoring/stats?device_id=PICO_CABAI_01
     */
    public function stats(Request $request)
    {
        $deviceId = $request->input('device_id');
        $cacheKey = 'stats_' . ($deviceId ?? 'all');
        
        // Cache for 5 seconds to reduce database load
        $statsData = cache()->remember($cacheKey, 5, function() use ($deviceId) {
            // Query latest data (dengan atau tanpa filter device_id)
            $query = Monitoring::latest();
            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }
            $latest = $query->first();
            
            $count = Monitoring::count();
            
            // Hitung uptime (asumsi: waktu dari record pertama)
            $firstRecord = Monitoring::oldest()->first();
            $uptime = $firstRecord ? Carbon::now()->diffInMinutes($firstRecord->created_at) : 0;
            $uptimeHours = floor($uptime / 60);
            $uptimeMinutes = $uptime % 60;

            // Average values (24 jam terakhir)
            $avgQuery = Monitoring::where('created_at', '>', Carbon::now()->subDay());
            if ($deviceId) {
                $avgQuery->where('device_id', $deviceId);
            }
            
            $avgTemp = $avgQuery->whereNotNull('temperature')->avg('temperature');

            // Ambil info device dari settings jika ada
            $deviceInfo = null;
            if ($latest && $latest->device_id) {
                $deviceInfo = \App\Models\DeviceSetting::where('device_id', $latest->device_id)->first();
            }

            // Cek apakah device online (data < 30 detik)
            $isOnline = $latest && $latest->updated_at->diffInSeconds(Carbon::now()) < 30;
            
            // Jika device offline, set semua hardware_status menjadi false
            $hardwareStatus = $latest->hardware_status ?? null;
            if (!$isOnline && $hardwareStatus) {
                $hardwareStatus = [
                    'dht22' => false,
                    'soil_sensor' => false,
                    'relay' => false,
                    'servo' => false,
                    'lcd' => false
                ];
            }

            return [
                'device_id' => $latest->device_id ?? null,
                'device_name' => $latest->device_name ?? 'Smart Garden',
                'plant_type' => $deviceInfo->plant_type ?? 'cabai',
                'mode' => $deviceInfo->mode ?? 1,
                'ip_address' => $latest->ip_address ?? null,
                'temperature' => $latest->temperature ?? 0,
                'soil_moisture' => $latest->soil_moisture ?? 0,
                'relay_status' => $latest->relay_status ?? false,
                'hardware_status' => $hardwareStatus,
                'raw_adc' => $latest->raw_adc ?? 0,
                'uptime_hours' => $uptimeHours,
                'uptime_minutes' => $uptimeMinutes,
                'total_records' => $count,
                'avg_temperature_24h' => round($avgTemp ?? 0, 1),
                'is_online' => $isOnline,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $statsData
        ], 200)->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                 ->header('Pragma', 'no-cache')
                 ->header('Expires', '0');
    }

    /**
     * Get logs untuk Activity Log page dengan deteksi perubahan status
     * Endpoint: GET /api/monitoring/logs?limit=20
     */
    public function logs(Request $request)
    {
        $limit = $request->input('limit', 50); // Ambil lebih banyak untuk deteksi perubahan
        
        // Cek apakah ada data terbaru dalam 30 detik terakhir
        $latestData = Monitoring::latest()->first();
        $isDeviceOnline = $latestData && $latestData->updated_at->diffInSeconds(Carbon::now()) < 30;
        
        // Jika device offline, tambahkan log warning di awal
        $offlineLog = null;
        if (!$isDeviceOnline && $latestData) {
            $offlineLog = [
                'id' => 'offline',
                'time' => Carbon::now()->format('H:i:s'),
                'date' => Carbon::now()->format('Y-m-d'),
                'level' => 'ERROR',
                'device' => $latestData->device_name ?? 'Pico W',
                'message' => '🔴 PICO W OFFLINE - Tidak ada data dalam 30 detik terakhir',
                'details' => 'Last seen: ' . $latestData->updated_at->format('H:i:s'),
            ];
        }
        
        $allData = Monitoring::latest()->take($limit)->get();
        $logs = collect();
        
        foreach ($allData as $index => $item) {
            $message = '';
            $level = 'INFO';
            $details = [];
            
            // Cek perubahan status relay dari data sebelumnya
            $previousItem = $allData->get($index + 1);
            $isRelayChanged = false;
            
            if ($previousItem) {
                // Deteksi perubahan relay status
                if ($item->relay_status != $previousItem->relay_status) {
                    $isRelayChanged = true;
                    
                    if ($item->relay_status) {
                        $message = '🟢 POMPA DINYALAKAN (Relay ON)';
                        $level = 'SUCCESS';
                        $details[] = "Soil: {$item->soil_moisture}%";
                        $details[] = "Temp: {$item->temperature}°C";
                        
                        // Cek penyebab pompa nyala
                        if ($item->soil_moisture < 25) {
                            $details[] = "⚠️ Tanah kering";
                        }
                    } else {
                        $message = '🔴 POMPA DIMATIKAN (Relay OFF)';
                        $level = 'INFO';
                        $details[] = "Soil: {$item->soil_moisture}%";
                        $details[] = "Temp: {$item->temperature}°C";
                        
                        if ($item->soil_moisture > 30) {
                            $details[] = "✓ Tanah sudah cukup lembab";
                        }
                    }
                }
                
                // Deteksi perubahan signifikan pada soil moisture (>10%)
                $soilDiff = abs($item->soil_moisture - $previousItem->soil_moisture);
                if (!$isRelayChanged && $soilDiff > 10) {
                    if ($item->soil_moisture > $previousItem->soil_moisture) {
                        $message = '💧 Kelembaban NAIK ' . round($soilDiff, 1) . '%';
                        $level = 'INFO';
                        $details[] = "Dari {$previousItem->soil_moisture}% → {$item->soil_moisture}%";
                    } else {
                        $message = '🌵 Kelembaban TURUN ' . round($soilDiff, 1) . '%';
                        $level = 'WARN';
                        $details[] = "Dari {$previousItem->soil_moisture}% → {$item->soil_moisture}%";
                    }
                    $details[] = "Relay: " . ($item->relay_status ? 'ON' : 'OFF');
                }
            }
            
            // Jika tidak ada perubahan, buat log normal
            if (empty($message)) {
                // Cek hardware_status dari Pico W
                $hwStatus = $item->hardware_status ?? null;
                $allOffline = false;
                
                if ($hwStatus && is_array($hwStatus)) {
                    // Cek apakah semua sensor offline (dengan pengecekan key exist)
                    $dht22Status = isset($hwStatus['dht22']) ? $hwStatus['dht22'] : true;
                    $soilStatus = isset($hwStatus['soil_sensor']) ? $hwStatus['soil_sensor'] : true;
                    $allOffline = !$dht22Status && !$soilStatus;
                }
                
                // Jika semua sensor offline
                if ($allOffline) {
                    $message = '⚠️ Semua sensor tidak terdeteksi';
                    $level = 'ERROR';
                } else {
                    // Log status normal
                    $relayStatus = $item->relay_status ? 'ON' : 'OFF';
                    $message = "Status: Relay {$relayStatus}";
                    $level = 'INFO';
                    
                    $details[] = "Soil: {$item->soil_moisture}%";
                    $details[] = "Temp: {$item->temperature}°C";
                    
                    // Warning untuk kondisi sensor
                    if ($hwStatus && is_array($hwStatus)) {
                        $sensorWarnings = [];
                        
                        if (isset($hwStatus['dht22']) && !$hwStatus['dht22']) {
                            $sensorWarnings[] = 'DHT22 offline';
                        }
                        if (isset($hwStatus['soil_sensor']) && !$hwStatus['soil_sensor']) {
                            $sensorWarnings[] = 'Soil sensor offline';
                        }
                        
                        if (!empty($sensorWarnings)) {
                            $details[] = '⚠️ ' . implode(', ', $sensorWarnings);
                            $level = 'WARN';
                        }
                    }
                    
                    // Alert kondisi ekstrem
                    if ($item->temperature && $item->temperature > 35) {
                        $details[] = '🔥 Suhu sangat tinggi!';
                        $level = 'WARN';
                    }
                    
                    if ($item->soil_moisture && $item->soil_moisture < 20) {
                        $details[] = '⚠️ Tanah sangat kering!';
                        $level = 'WARN';
                    }
                }
            }

            $logs->push([
                'id' => $item->id,
                'time' => $item->created_at->format('H:i:s'),
                'date' => $item->created_at->format('Y-m-d'),
                'level' => $level,
                'device' => $item->device_name ?? 'System',
                'message' => $message,
                'details' => implode(' | ', $details),
                'soil_moisture' => $item->soil_moisture,
                'temperature' => $item->temperature,
                'relay_status' => $item->relay_status,
            ]);
        }
        
        // Ambil hanya 20 log terbaru (setelah filtering)
        $logs = $logs->take(20);
        
        // Tambahkan log offline di awal jika device offline
        if ($offlineLog) {
            $logs = collect([$offlineLog])->merge($logs);
        }

        return response()->json([
            'success' => true,
            'count' => $logs->count(),
            'data' => $logs
        ], 200)->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                 ->header('Pragma', 'no-cache')
                 ->header('Expires', '0');
    }

    /**
     * Data untuk Dashboard Frontend (Multi-Device dengan Settings)
     * Endpoint: GET /api/monitoring
     * 
     * Mengembalikan data terakhir dari SETIAP device_id unik
     * dengan join ke tabel device_settings dan status online/offline
     */
    public function api_show()
    {
        // Ambil data terakhir dari SETIAP device_id unik
        // Join dengan tabel device_settings agar frontend tahu Mode & Kalibrasi
        $data = DB::table('monitorings as m')
            ->leftJoin('device_settings as s', 'm.device_id', '=', 's.device_id')
            ->select(
                'm.*',
                's.id as setting_id',
                's.mode',
                's.batas_siram',
                's.batas_stop',
                's.jam_pagi',
                's.jam_sore',
                's.durasi_siram',
                's.sensor_min as min_kering',
                's.sensor_max as max_basah',
                's.plant_type',
                's.firmware_version',
                's.last_seen'
            )
            ->whereIn('m.id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('monitorings')
                      ->groupBy('device_id');
            })
            ->get()
            ->map(function($item) {
                // Status online berdasarkan updated_at dari tabel monitorings
                $updatedAt = $item->updated_at ? Carbon::parse($item->updated_at) : null;
                $item->is_online = $updatedAt ? $updatedAt->diffInSeconds(Carbon::now()) < 30 : false;
                return $item;
            });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data
        ]);
    }

    /**
     * Update Setting dari Modal Frontend
     * Endpoint: POST /api/settings/update
     * 
     * Compatible dengan format lama untuk backward compatibility
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'mode' => 'nullable|integer|in:1,2,3,4',
            'batas_kering' => 'nullable|integer|min:0|max:100',
            'batas_siram' => 'nullable|integer|min:0|max:100',
            'batas_stop' => 'nullable|integer|min:0|max:100',
            'jam_pagi' => 'nullable|date_format:H:i',
            'jam_sore' => 'nullable|date_format:H:i',
            'durasi_siram' => 'nullable|integer|min:1|max:60',
            'min_kering' => 'nullable|integer|min:0|max:4095',
            'max_basah' => 'nullable|integer|min:0|max:4095',
            'sensor_min' => 'nullable|integer|min:0|max:4095',
            'sensor_max' => 'nullable|integer|min:0|max:4095',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari atau buat setting untuk device ini (Auto-provisioning)
        $setting = \App\Models\DeviceSetting::firstOrCreate(
            ['device_id' => $request->device_id],
            ['mode' => 1] // Default Mode Basic
        );

        // Update field yang dikirim (support field names lama & baru)
        $updateData = [];
        
        // Mode
        if ($request->has('mode')) {
            $updateData['mode'] = $request->mode;
        }
        
        // Threshold (support both naming conventions)
        if ($request->has('batas_kering')) {
            $updateData['batas_siram'] = $request->batas_kering;
        }
        if ($request->has('batas_siram')) {
            $updateData['batas_siram'] = $request->batas_siram;
        }
        if ($request->has('batas_stop')) {
            $updateData['batas_stop'] = $request->batas_stop;
        }
        
        // Schedule
        if ($request->has('jam_pagi')) {
            $updateData['jam_pagi'] = $request->jam_pagi;
        }
        if ($request->has('jam_sore')) {
            $updateData['jam_sore'] = $request->jam_sore;
        }
        if ($request->has('durasi_siram')) {
            $updateData['durasi_siram'] = $request->durasi_siram;
        }
        
        // Calibration (support both naming conventions)
        if ($request->has('min_kering')) {
            $updateData['sensor_min'] = $request->min_kering;
        }
        if ($request->has('max_basah')) {
            $updateData['sensor_max'] = $request->max_basah;
        }
        if ($request->has('sensor_min')) {
            $updateData['sensor_min'] = $request->sensor_min;
        }
        if ($request->has('sensor_max')) {
            $updateData['sensor_max'] = $request->sensor_max;
        }

        // Update setting
        if (!empty($updateData)) {
            $setting->update($updateData);
        }

        return response()->json([
            'success' => true,
            'status' => 'success', // Backward compatibility
            'message' => 'Setting berhasil diupdate',
            'data' => $setting->fresh()
        ]);
    }

    /**
     * Manual Calibration untuk Soil Sensor
     * Endpoint: POST /api/devices/{deviceId}/calibrate
     * Body: { "adc_kering": 2000, "adc_basah": 35000 }
     */
    public function calibrateSensor(Request $request, $deviceId)
    {
        $validator = Validator::make($request->all(), [
            'adc_kering' => 'required|integer|min:0|max:65535',
            'adc_basah' => 'required|integer|min:0|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi: ADC kering harus lebih besar dari ADC basah
        if ($request->adc_kering <= $request->adc_basah) {
            return response()->json([
                'success' => false,
                'message' => 'ADC Kering harus lebih besar dari ADC Basah'
            ], 422);
        }

        // Update calibration values
        $setting = \App\Models\DeviceSetting::where('device_id', $deviceId)->first();
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        $setting->update([
            'sensor_min' => $request->adc_kering,  // Kering = ADC tinggi
            'sensor_max' => $request->adc_basah,   // Basah = ADC rendah
        ]);

        cache()->forget('device_setting_' . $deviceId);

        Log::info("🎯 MANUAL CALIBRATION - Device: {$deviceId}, Kering: {$request->adc_kering}, Basah: {$request->adc_basah}");

        return response()->json([
            'success' => true,
            'message' => 'Kalibrasi berhasil diupdate',
            'data' => [
                'device_id' => $deviceId,
                'sensor_min' => $request->adc_kering,
                'sensor_max' => $request->adc_basah,
                'range' => $request->adc_kering - $request->adc_basah
            ]
        ], 200);
    }

    /**
     * Reset Calibration (Force Auto-Calibration)
     * Endpoint: POST /api/devices/{deviceId}/calibrate/reset
     */
    public function resetCalibration($deviceId)
    {
        $setting = \App\Models\DeviceSetting::where('device_id', $deviceId)->first();
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        // Reset ke nilai default untuk trigger auto-calibration
        $setting->update([
            'sensor_min' => 4095,
            'sensor_max' => 1500,
        ]);

        cache()->forget('device_setting_' . $deviceId);

        Log::info("🔄 CALIBRATION RESET - Device: {$deviceId} - Will auto-calibrate on next 20 samples");

        return response()->json([
            'success' => true,
            'message' => 'Kalibrasi direset, sistem akan auto-kalibrasi dalam 20 sample berikutnya'
        ], 200);
    }

    /**
     * Check relay command (Fast polling endpoint untuk Pico W)
     * Endpoint: GET /api/monitoring/check-command?device_id=PICO_CABAI_01&relay_status=0
     * Response ringan, hanya return relay_command jika ada
     */
    public function checkCommand(Request $request)
    {
        $deviceId = $request->device_id ?? 'PICO_CABAI_01';
        $currentRelayStatus = (int)$request->relay_status;
        
        // Ambil device settings
        $setting = \App\Models\DeviceSetting::where('device_id', $deviceId)->first();
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }
        
        $response = [
            'success' => true,
            'device_id' => $deviceId
        ];
        
        // relay_command: null = no pending command, 1 = turn ON, 0 = turn OFF
        // Cast to nullable integer (not boolean)
        if ($setting->relay_command !== null) {
            $commandValue = (int)$setting->relay_command;
            
            // Hanya kirim command jika berbeda dengan status sekarang
            if ($commandValue !== $currentRelayStatus) {
                $response['relay_command'] = $commandValue;
                Log::info("⚡ FAST CHECK - Command found: {$commandValue} for {$deviceId}");
            } else {
                // Command sudah dijalankan - reset ke null
                $setting->update(['relay_command' => null]);
                cache()->forget('device_setting_' . $deviceId);
                Log::info("✅ FAST CHECK - Command executed, reset to null for {$deviceId}");
            }
        }
        
        return response()->json($response, 200);
    }
}
