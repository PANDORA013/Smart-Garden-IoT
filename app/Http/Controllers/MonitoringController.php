<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
        // Validasi input (flexible untuk backward compatibility)
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:100',
            'connected_devices' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:-50|max:100',
            'soil_moisture' => 'nullable|numeric|min:0|max:100',
            'raw_adc' => 'nullable|integer|min:0|max:4095',
            'relay_status' => 'nullable|boolean',
            'status_pompa' => 'nullable|string|in:Hidup,Mati',
            'device_name' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
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
            'connected_devices' => $request->connected_devices,
            'device_name' => $request->device_name ?? $request->device_id,
            'temperature' => $request->temperature,
            'soil_moisture' => $request->soil_moisture,
            'raw_adc' => $request->raw_adc,
            'relay_status' => $request->relay_status ?? ($request->status_pompa === 'Hidup'),
            'status_pompa' => $request->status_pompa ?? ($request->relay_status ? 'Hidup' : 'Mati'),
            'ip_address' => $request->ip_address,
        ];

        $monitoring = Monitoring::create($data);

        // 2. AMBIL/BUAT KONFIGURASI (Auto-Provisioning)
        $setting = \App\Models\DeviceSetting::firstOrCreate(
            ['device_id' => $request->device_id],
            [
                'device_name' => $request->device_name ?? $request->device_id,
                'mode' => 1, // Default: Basic Threshold
                'sensor_min' => 4095, // Default: Sensor kering di udara
                'sensor_max' => 1500, // Default: Sensor basah di air
                'batas_siram' => 40,
                'batas_stop' => 70,
            ]
        );

        // Update last_seen
        $setting->update(['last_seen' => now()]);

        // 3. KIRIM KONFIGURASI BALIK KE PICO (2-Way Communication)
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $monitoring,
            
            // === CONFIG UNTUK PICO (Otak Cerdas) ===
            'config' => [
                'mode' => $setting->mode,
                
                // Kalibrasi ADC (Pico gunakan ini untuk konversi ADC → %)
                'adc_min' => $setting->sensor_min,
                'adc_max' => $setting->sensor_max,
                
                // Threshold Mode 1 (Basic)
                'batas_kering' => $setting->batas_siram,
                'batas_basah' => $setting->batas_stop,
                
                // Schedule Mode 3
                'jam_pagi' => substr($setting->jam_pagi, 0, 5), // "07:00"
                'jam_sore' => substr($setting->jam_sore, 0, 5), // "17:00"
                'durasi_siram' => $setting->durasi_siram,
            ]
        ], 201);
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
                ]
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $latest
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Simpan status baru
        $monitoring = Monitoring::create([
            'relay_status' => $request->status,
            'status_pompa' => $request->status ? 'Hidup' : 'Mati',
            'device_name' => 'Manual Control',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Relay status updated',
            'data' => $monitoring
        ], 200);
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
                'uptime_hours' => $uptimeHours,
                'uptime_minutes' => $uptimeMinutes,
                'total_records' => $count,
                'avg_temperature_24h' => round($avgTemp ?? 0, 1),
            ]
        ], 200);
    }

    /**
     * Get logs untuk Activity Log page
     * Endpoint: GET /api/monitoring/logs?limit=20
     */
    public function logs(Request $request)
    {
        $limit = $request->input('limit', 20);
        
        $logs = Monitoring::latest()
            ->take($limit)
            ->get()
            ->map(function ($item) {
                // Generate log message berdasarkan data
                $message = '';
                $level = 'INFO';
                
                if ($item->relay_status) {
                    $message = 'Relay/Pompa diaktifkan';
                    $level = 'SUCCESS';
                } else {
                    $message = 'Relay/Pompa dimatikan';
                }
                
                if ($item->temperature && $item->temperature > 33) {
                    $message .= " | Suhu tinggi terdeteksi ({$item->temperature}°C)";
                    $level = 'WARN';
                }
                
                if ($item->soil_moisture && $item->soil_moisture < 30) {
                    $message .= " | Kelembaban tanah rendah ({$item->soil_moisture}%)";
                    $level = 'WARN';
                }

                return [
                    'id' => $item->id,
                    'time' => $item->created_at->format('H:i:s'),
                    'date' => $item->created_at->format('Y-m-d'),
                    'level' => $level,
                    'device' => $item->device_name ?? 'System',
                    'message' => $message ?: 'Data monitoring diterima',
                    'temperature' => $item->temperature,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $logs->count(),
            'data' => $logs
        ], 200);
    }

    /**
     * Data untuk Dashboard Frontend (Multi-Device dengan Settings)
     * Endpoint: GET /api/monitoring
     * 
     * Mengembalikan data terakhir dari SETIAP device_id unik
     * dengan join ke tabel device_settings
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
                's.firmware_version'
            )
            ->whereIn('m.id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('monitorings')
                      ->groupBy('device_id');
            })
            ->get();

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
}
