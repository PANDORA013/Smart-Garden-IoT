<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\Device;
use App\Models\DeviceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get summary statistics
        $stats = [
            'total_devices' => DeviceSetting::count(),
            // Online = last data received within 2 minutes (consistent with DeviceSetting::isOnline fallback)
            'online_devices' => DeviceSetting::where(function($q) {
                $q->where('last_seen_at', '>=', Carbon::now()->subMinutes(2))
                  ->orWhere('last_seen', '>=', Carbon::now()->subMinutes(2));
            })->count(),
            'total_logs' => Monitoring::count(),
            'active_relays' => Monitoring::where('relay_status', true)
                ->where('created_at', '>=', Carbon::now()->subMinutes(2))
                ->distinct('device_id')
                ->count()
        ];        
        // Get recent devices
        $recentDevices = DeviceSetting::orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        // Get latest sensor data per device
        $latestData = DB::table('monitorings')
            ->select('*')
            ->whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('monitorings')
                      ->groupBy('device_id');
            })
            ->get();
        
        return view('dashboard.index', compact('stats', 'recentDevices', 'latestData'));
    }
    
    /**
     * Get dashboard statistics (API endpoint)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $stats = [
            'devices' => [
                'total' => DeviceSetting::count(),
                'online' => DeviceSetting::where(function($q) {
                    $q->where('last_seen_at', '>=', Carbon::now()->subMinutes(2))
                      ->orWhere('last_seen', '>=', Carbon::now()->subMinutes(2));
                })->count(),
                'offline' => DeviceSetting::where(function($q) {
                    $q->where('last_seen_at', '<', Carbon::now()->subMinutes(2))
                      ->orWhereNull('last_seen_at');
                })->where(function($q) {
                    $q->where('last_seen', '<', Carbon::now()->subMinutes(2))
                      ->orWhereNull('last_seen');
                })->count(),
            ],
            'monitoring' => [
                'total_logs' => Monitoring::count(),
                'today_logs' => Monitoring::whereDate('created_at', Carbon::today())->count(),
                'week_logs' => Monitoring::where('created_at', '>=', Carbon::now()->subWeek())->count(),
            ],
            'relays' => [
                'active' => Monitoring::where('relay_status', true)
                    ->where('created_at', '>=', Carbon::now()->subMinutes(2))
                    ->distinct('device_id')
                    ->count(),
            ],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
