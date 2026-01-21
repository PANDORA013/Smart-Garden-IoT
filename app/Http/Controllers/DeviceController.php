<?php

namespace App\Http\Controllers;

use App\Models\DeviceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * AUTO-PROVISIONING ENDPOINT
     * Arduino check-in untuk mendapatkan konfigurasi
     * Jika device baru, otomatis dibuatkan dengan default cabai settings
     * 
     * Endpoint: GET /api/device/check-in?device_id=CABAI_01&firmware=v1.0
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:50',
            'firmware' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $deviceId = $request->device_id;
        $firmware = $request->firmware;

        // FITUR UTAMA: AUTO-DETECT & AUTO-CALIBRATE
        // Jika alat baru, otomatis dibuatkan dengan settingan default Cabai
        $setting = DeviceSetting::firstOrCreate(
            ['device_id' => $deviceId],
            array_merge(
                DeviceSetting::cabaiDefaults(),
                [
                    'device_name' => $deviceId,
                    'firmware_version' => $firmware,
                    'last_seen' => now(),
                ]
            )
        );

        // Update last_seen dan firmware setiap check-in
        $setting->update([
            'last_seen' => now(),
            'firmware_version' => $firmware ?? $setting->firmware_version,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device configuration retrieved',
            'is_new_device' => $setting->wasRecentlyCreated,
            'config' => [
                'device_id' => $setting->device_id,
                'device_name' => $setting->device_name,
                'plant_type' => $setting->plant_type,
                'mode' => $setting->mode,
                'sensor_min' => $setting->sensor_min,
                'sensor_max' => $setting->sensor_max,
                'batas_siram' => $setting->batas_siram,
                'batas_stop' => $setting->batas_stop,
                'jam_pagi' => $setting->jam_pagi,
                'jam_sore' => $setting->jam_sore,
                'durasi_siram' => $setting->durasi_siram,
                'is_active' => $setting->is_active,
            ]
        ], 200);
    }

    /**
     * Get all devices
     * Endpoint: GET /api/devices
     */
    public function index()
    {
        $devices = DeviceSetting::orderBy('last_seen', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $devices->count(),
            'data' => $devices->map(function ($device) {
                // Ambil IP address dari monitoring terakhir
                $latestMonitoring = \App\Models\Monitoring::where('device_id', $device->device_id)
                    ->latest()
                    ->first();
                
                return [
                    'id' => $device->id,
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'plant_type' => $device->plant_type,
                    'mode' => $device->mode,
                    'sensor_min' => $device->sensor_min,
                    'sensor_max' => $device->sensor_max,
                    'batas_siram' => $device->batas_siram,
                    'batas_stop' => $device->batas_stop,
                    'jam_pagi' => $device->jam_pagi,
                    'jam_sore' => $device->jam_sore,
                    'durasi_siram' => $device->durasi_siram,
                    'firmware_version' => $device->firmware_version,
                    'is_active' => $device->is_active,
                    'last_seen' => $device->last_seen,
                    'ip_address' => $latestMonitoring->ip_address ?? null,
                    'hardware_status' => $latestMonitoring->hardware_status ?? null,
                    'status' => $this->getDeviceStatus($device),
                ];
            })
        ], 200)->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                 ->header('Pragma', 'no-cache')
                 ->header('Expires', '0');
    }

    /**
     * Get single device detail
     * Endpoint: GET /api/devices/{id}
     */
    public function show($id)
    {
        // Support both integer ID and string device_id
        $device = is_numeric($id) 
            ? DeviceSetting::findOrFail($id)
            : DeviceSetting::where('device_id', $id)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $device
        ], 200);
    }

    /**
     * Update device settings
     * Endpoint: PUT /api/devices/{id}
     */
    public function update(Request $request, $id)
    {
        // Support both integer ID and string device_id
        $device = is_numeric($id) 
            ? DeviceSetting::findOrFail($id)
            : DeviceSetting::where('device_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'device_name' => 'nullable|string|max:100',
            'plant_type' => 'nullable|string|max:50',
            'sensor_min' => 'nullable|integer|min:0|max:4095',
            'sensor_max' => 'nullable|integer|min:0|max:4095',
            'batas_siram' => 'nullable|integer|min:0|max:100',
            'batas_stop' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $device->update($request->only([
            'device_name',
            'plant_type',
            'sensor_min',
            'sensor_max',
            'batas_siram',
            'batas_stop',
            'is_active',
            'notes',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Device settings updated successfully',
            'data' => $device
        ], 200);
    }

    /**
     * Delete device
     * Endpoint: DELETE /api/devices/{id}
     */
    public function destroy($id)
    {
        // Support both integer ID and string device_id
        $device = is_numeric($id) 
            ? DeviceSetting::findOrFail($id)
            : DeviceSetting::where('device_id', $id)->firstOrFail();
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully'
        ], 200);
    }

    /**
     * Apply preset (cabai atau tomat)
     * Endpoint: POST /api/devices/{id}/preset
     */
    public function applyPreset(Request $request, $id)
    {
        // Support both integer ID and string device_id
        $device = is_numeric($id) 
            ? DeviceSetting::findOrFail($id)
            : DeviceSetting::where('device_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'preset' => 'required|string|in:cabai,tomat',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $preset = $request->preset === 'cabai' 
            ? DeviceSetting::cabaiDefaults()
            : DeviceSetting::tomatDefaults();

        $device->update($preset);

        return response()->json([
            'success' => true,
            'message' => "Preset {$request->preset} applied successfully",
            'data' => $device
        ], 200);
    }

    /**
     * Update operating mode dan parameter terkait
     * Endpoint: POST /api/devices/{id}/mode
     */
    public function updateMode(Request $request, $id)
    {
        // Support both integer ID and string device_id
        $device = is_numeric($id) 
            ? DeviceSetting::findOrFail($id)
            : DeviceSetting::where('device_id', $id)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'mode' => 'required|integer|in:1,2,3,4',
            'batas_siram' => 'nullable|integer|min:0|max:100',
            'batas_stop' => 'nullable|integer|min:0|max:100',
            'jam_pagi' => 'nullable|date_format:H:i',
            'jam_sore' => 'nullable|date_format:H:i',
            'durasi_siram' => 'nullable|integer|min:1|max:60',
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

        // Update mode
        $updateData = ['mode' => $request->mode];

        // Update kalibrasi ADC (berlaku untuk semua mode)
        if ($request->has('sensor_min')) {
            $updateData['sensor_min'] = $request->sensor_min;
        }
        if ($request->has('sensor_max')) {
            $updateData['sensor_max'] = $request->sensor_max;
        }

        // Update parameter berdasarkan mode
        if ($request->mode == 1) {
            // Mode Pemula: Force to standard (auto-set by smart config)
            if ($request->has('batas_siram')) {
                $updateData['batas_siram'] = $request->batas_siram;
            }
            if ($request->has('batas_stop')) {
                $updateData['batas_stop'] = $request->batas_stop;
            }
        } elseif ($request->mode == 3) {
            // Mode Schedule: Update jadwal
            if ($request->has('jam_pagi')) {
                $updateData['jam_pagi'] = $request->jam_pagi;
            }
            if ($request->has('jam_sore')) {
                $updateData['jam_sore'] = $request->jam_sore;
            }
            if ($request->has('durasi_siram')) {
                $updateData['durasi_siram'] = $request->durasi_siram;
            }
        } elseif ($request->mode == 4) {
            // Mode Manual: User-defined thresholds
            if ($request->has('batas_siram')) {
                $updateData['batas_siram'] = $request->batas_siram;
            }
            if ($request->has('batas_stop')) {
                $updateData['batas_stop'] = $request->batas_stop;
            }
            
            // Validation: batas_stop must be greater than batas_siram
            if (isset($updateData['batas_stop']) && isset($updateData['batas_siram'])) {
                if ($updateData['batas_stop'] <= $updateData['batas_siram']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Batas Basah (OFF) harus lebih tinggi dari Batas Kering (ON)'
                    ], 422);
                }
            }
        }
        // Mode 2 (Fuzzy) tidak ada parameter tambahan

        $device->update($updateData);

        $modeName = [
            '1' => 'Mode Pemula (Basic)', 
            '2' => 'Mode AI (Fuzzy Logic)', 
            '3' => 'Mode Terjadwal (Schedule)',
            '4' => 'Mode Manual'
        ][$request->mode];

        return response()->json([
            'success' => true,
            'message' => "Mode berhasil diubah ke {$modeName}",
            'data' => $device
        ], 200);
    }

    /**
     * Get device status (online/offline)
     */
    private function getDeviceStatus(DeviceSetting $device): string
    {
        // Cek dari monitoring terbaru, bukan dari last_seen di DeviceSetting
        $latestMonitoring = \App\Models\Monitoring::where('device_id', $device->device_id)
            ->latest()
            ->first();
        
        if (!$latestMonitoring) {
            return 'never_connected';
        }

        $secondsAgo = $latestMonitoring->updated_at->diffInSeconds(now());

        // Online jika data < 30 detik (sinkron dengan MonitoringController)
        if ($secondsAgo < 30) {
            return 'online';
        } elseif ($secondsAgo < 120) {
            return 'idle';
        } else {
            return 'offline';
        }
    }
}
