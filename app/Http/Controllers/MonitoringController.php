<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends Controller
{
    /**
     * Insert data dari ESP32/Arduino
     * Endpoint: POST /api/monitoring/insert
     * 
     * Expected JSON from microcontroller (Universal IoT):
     * {
     *   "temperature": 28.5,
     *   "humidity": 64.0,
     *   "soil_moisture": 35.5,
     *   "relay_status": true,
     *   "device_name": "ESP32-Main",
     *   "ip_address": "192.168.1.105"
     * }
     * 
     * Backward compatible dengan format lama:
     * {
     *   "soil_moisture": 35.5,
     *   "status_pompa": "Hidup"
     * }
     */
    public function insert(Request $request)
    {
        // Validasi input (flexible untuk backward compatibility)
        $validator = Validator::make($request->all(), [
            'temperature' => 'nullable|numeric|min:-50|max:100',
            'humidity' => 'nullable|numeric|min:0|max:100',
            'soil_moisture' => 'nullable|numeric|min:0|max:100',
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

        // Map data untuk backward compatibility
        $data = [
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'soil_moisture' => $request->soil_moisture,
            'relay_status' => $request->relay_status ?? ($request->status_pompa === 'Hidup' ? true : false),
            'device_name' => $request->device_name,
            'ip_address' => $request->ip_address,
            'status_pompa' => $request->status_pompa ?? ($request->relay_status ? 'Hidup' : 'Mati'),
        ];

        // Simpan ke database
        $monitoring = Monitoring::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $monitoring
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
                    'humidity' => 0,
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
     * Get statistics untuk dashboard
     * Endpoint: GET /api/monitoring/stats
     */
    public function stats()
    {
        $latest = Monitoring::latest()->first();
        $count = Monitoring::count();
        
        // Hitung uptime (asumsi: waktu dari record pertama)
        $firstRecord = Monitoring::oldest()->first();
        $uptime = $firstRecord ? now()->diffInMinutes($firstRecord->created_at) : 0;
        $uptimeHours = floor($uptime / 60);
        $uptimeMinutes = $uptime % 60;

        // Average values (24 jam terakhir)
        $avgTemp = Monitoring::where('created_at', '>', now()->subDay())
            ->whereNotNull('temperature')
            ->avg('temperature');
        
        $avgHumidity = Monitoring::where('created_at', '>', now()->subDay())
            ->whereNotNull('humidity')
            ->avg('humidity');

        return response()->json([
            'success' => true,
            'data' => [
                'temperature' => $latest->temperature ?? 0,
                'humidity' => $latest->humidity ?? 0,
                'soil_moisture' => $latest->soil_moisture ?? 0,
                'relay_status' => $latest->relay_status ?? false,
                'uptime_hours' => $uptimeHours,
                'uptime_minutes' => $uptimeMinutes,
                'total_records' => $count,
                'avg_temperature_24h' => round($avgTemp ?? 0, 1),
                'avg_humidity_24h' => round($avgHumidity ?? 0, 1),
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
                    'humidity' => $item->humidity,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $logs->count(),
            'data' => $logs
        ], 200);
    }
}
