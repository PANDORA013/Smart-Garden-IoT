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
     * Expected JSON from microcontroller:
     * {
     *   "soil_moisture": 35.5,
     *   "status_pompa": "Hidup"
     * }
     */
    public function insert(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'soil_moisture' => 'required|numeric|min:0|max:100',
            'status_pompa' => 'required|string|in:Hidup,Mati',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Simpan ke database
        $monitoring = Monitoring::create([
            'soil_moisture' => $request->soil_moisture,
            'status_pompa' => $request->status_pompa,
        ]);

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
                    'soil_moisture' => 0,
                    'status_pompa' => 'Mati'
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
}
