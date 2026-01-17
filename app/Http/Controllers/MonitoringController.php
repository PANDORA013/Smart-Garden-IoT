<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'soil_moisture' => 'nullable|numeric|min:0|max:100',
            'raw_adc' => 'nullable|integer|min:0|max:65535', // Update: Support 16-bit ADC
            'relay_status' => 'nullable|boolean',
            'device_name' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'hardware_status' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. SIMPAN DATA SENSOR
        $data = [
            'device_id' => $request->device_id,
            'device_name' => $request->device_name ?? $request->device_id,
            'temperature' => $request->temperature,
            'soil_moisture' => $request->soil_moisture,
            'raw_adc' => $request->raw_adc,
            'relay_status' => $request->relay_status ?? false,
            'status_pompa' => $request->relay_status ? 'Hidup' : 'Mati',
            'ip_address' => $request->ip_address,
            'hardware_status' => $request->hardware_status ?? null,
        ];

        $monitoring = Monitoring::create($data);

        // 2. AMBIL/BUAT KONFIGURASI (Auto-Provisioning) - OPTIMIZED dengan Cache
        $cacheKey = 'device_setting_' . $request->device_id;
        
        $setting = cache()->remember($cacheKey, 60, function() use ($request) {
            return \App\Models\DeviceSetting::firstOrCreate(
                ['device_id' => $request->device_id],
                [
                    'device_name' => $request->device_name ?? $request->device_id,
                    'mode' => 1,
                    'sensor_min' => 4095,
                    'sensor_max' => 1500,
                    'batas_siram' => 20,
                    'batas_stop' => 30,
                ]
            );
        });
        
        // ===== AUTO CALIBRATION SYSTEM =====
        // Jika nilai ADC masih default (4095/1500), sistem perlu kalibrasi
        // Kalibrasi dilakukan dengan mengumpulkan sample ADC dari sensor
        $needsCalibration = ($setting->sensor_min == 4095 && $setting->sensor_max == 1500);
        
        if ($needsCalibration && $request->raw_adc) {
            // Mode: Auto-learning dari sample data
            // Ambil 20 sample terakhir untuk analisis
            $recentSamples = Monitoring::where('device_id', $request->device_id)
                ->whereNotNull('raw_adc')
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->pluck('raw_adc');
            
            // Jika sudah ada minimal 20 sample, kalkulasi min/max
            if ($recentSamples->count() >= 20) {
                $minADC = $recentSamples->min();
                $maxADC = $recentSamples->max();
                $avgADC = $recentSamples->avg();
                
                // Validasi: ADC range harus masuk akal (perbedaan minimal 5000)
                if (($maxADC - $minADC) > 5000) {
                    // Update sensor_min (kering) dan sensor_max (basah)
                    // Catatan: sensor_min = ADC tertinggi (kering), sensor_max = ADC terendah (basah)
                    $setting->update([
                        'sensor_min' => $maxADC,  // Kering = ADC tinggi
                        'sensor_max' => $minADC,  // Basah = ADC rendah
                    ]);
                    
                    cache()->forget($cacheKey);
                    
                    Log::info("🎯 AUTO CALIBRATION SUCCESS - Device: {$request->device_id}, Min(Wet): {$minADC}, Max(Dry): {$maxADC}, Avg: " . round($avgADC));
                } else {
                    Log::warning("⚠️ AUTO CALIBRATION SKIPPED - Device: {$request->device_id}, Range too small: " . ($maxADC - $minADC));
                }
            } else {
                Log::info("📊 Collecting calibration samples - Device: {$request->device_id}, Current: {$recentSamples->count()}/20");
            }
        }

        // Update last_seen hanya setiap 30 detik (skip jika baru saja update)
        if (!$setting->last_seen || $setting->last_seen->diffInSeconds(now()) > 30) {
            $setting->update(['last_seen' => now()]);
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
            Log::info("✅ MANUAL COMMAND - relay_command: {$setting->relay_command}");
            
            // Reset HANYA jika relay_status dari Pico W MATCH dengan command
            $currentRelayStatus = (int)$request->relay_status;
            $commandValue = (int)$setting->relay_command;
            
            if ($currentRelayStatus === $commandValue) {
                // Command sudah dijalankan - reset ke null
                $setting->update(['relay_command' => null]);
                cache()->forget($cacheKey);
                Log::info("✅ Manual command executed and reset - Device: {$request->device_id}");
            } else {
                Log::info("⏳ Waiting for manual command execution - Command: {$commandValue}, Current: {$currentRelayStatus}");
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
                // Durasi siram sesuai durasi_siram (dalam detik)
                // CATATAN: Logika schedule lebih kompleks, butuh tracking waktu terakhir siram
                //          Untuk saat ini, gunakan threshold sederhana sebagai fallback
                
                $currentHour = (int)date('H');
                $jamPagi = (int)date('H', strtotime($setting->jam_pagi));
                $jamSore = (int)date('H', strtotime($setting->jam_sore));
                
                // Sederhana: Jika jam sekarang = jam pagi/sore DAN tanah < 50% -> siram
                if (($currentHour == $jamPagi || $currentHour == $jamSore) && $soilMoisture < 50) {
                    $autoCommand = 1;
                    Log::info("⏰ MODE 3 SCHEDULE: Time {$currentHour}:00, Soil {$soilMoisture}% -> Pump ON");
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
     * Endpoint: GET /api/monitoring/latest
     */
    public function latest()
    {
        $latest = Monitoring::latest()->first();

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
        
        $history = Monitoring::latest()
            ->take($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'count' => $history->count(),
            'data' => $history
        ], 200);
    }

    /**
     * Hapus data lama (cleanup)
     * Endpoint: DELETE /api/monitoring/cleanup?days=7
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 7);
        
        $deleted = Monitoring::where('created_at', '<', now()->subDays($days))->delete();

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
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
            'device_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Ambil device_id (default ke PICO_CABAI_01)
        $deviceId = $request->device_id ?? 'PICO_CABAI_01';
        
        // Set relay command di device_settings
        $setting = \App\Models\DeviceSetting::where('device_id', $deviceId)->first();
        
        Log::info("🔧 toggleRelay called - Device: {$deviceId}, status: " . ($request->status ? 'ON' : 'OFF') . ", setting found: " . ($setting ? 'YES' : 'NO'));
        
        if ($setting) {
            Log::info("📝 BEFORE UPDATE - relay_command: " . var_export($setting->relay_command, true));
            
            $setting->update(['relay_command' => $request->status]);
            
            // Re-fetch to verify
            $setting->refresh();
            Log::info("✅ AFTER UPDATE - relay_command: " . var_export($setting->relay_command, true));
            
            cache()->forget('device_setting_' . $deviceId);
            
            return response()->json([
                'success' => true,
                'message' => 'Relay command sent',
                'relay_command' => $request->status
            ], 200);
        }
        
        Log::error("❌ toggleRelay FAILED - Device not found: {$deviceId}");
        
        return response()->json([
            'success' => false,
            'message' => 'Device not found'
        ], 404);
    }

    /**
     * Get statistics untuk dashboard (Multi-Device Support)
     * Endpoint: GET /api/monitoring/stats?device_id=PICO_CABAI_01
     */
    public function stats(Request $request)
    {
        $deviceId = $request->input('device_id');
        
        // Query latest data (dengan atau tanpa filter device_id)
        $query = Monitoring::latest();
        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }
        $latest = $query->first();
        
        $count = Monitoring::count();
        
        // Hitung uptime (asumsi: waktu dari record pertama)
        $firstRecord = Monitoring::oldest()->first();
        $uptime = $firstRecord ? now()->diffInMinutes($firstRecord->created_at) : 0;
        $uptimeHours = floor($uptime / 60);
        $uptimeMinutes = $uptime % 60;

        // Average values (24 jam terakhir)
        $avgQuery = Monitoring::where('created_at', '>', now()->subDay());
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
        $isOnline = $latest && $latest->updated_at->diffInSeconds(now()) < 30;
        
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

        return response()->json([
            'success' => true,
            'data' => [
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
            ]
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
        $isDeviceOnline = $latestData && $latestData->updated_at->diffInSeconds(now()) < 30;
        
        // Jika device offline, tambahkan log warning di awal
        $offlineLog = null;
        if (!$isDeviceOnline && $latestData) {
            $offlineLog = [
                'id' => 'offline',
                'time' => now()->format('H:i:s'),
                'date' => now()->format('Y-m-d'),
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
                $updatedAt = $item->updated_at ? \Carbon\Carbon::parse($item->updated_at) : null;
                $item->is_online = $updatedAt ? $updatedAt->diffInSeconds(now()) < 30 : false;
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
}
