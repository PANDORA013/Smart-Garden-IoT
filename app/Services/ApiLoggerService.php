<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Enhanced API Logger Service
 * Mencatat semua API activity, errors, dan performance metrics
 */
class ApiLoggerService
{
    /**
     * Log API request dengan detail lengkap
     */
    public static function logRequest(Request $request, string $action, array $additionalData = [])
    {
        Log::channel('api')->info('API Request', [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'input' => self::sanitizeInput($request->all()),
            ...$additionalData,
        ]);
    }

    /**
     * Log successful API response
     */
    public static function logSuccess(string $action, array $data = [], int $statusCode = 200, float $duration = 0)
    {
        Log::channel('api')->info('API Success', [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'status_code' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
            'data' => $data,
        ]);
    }

    /**
     * Log API error dengan full context
     */
    public static function logError(string $action, \Exception $exception, array $context = [], int $statusCode = 500)
    {
        Log::channel('api')->error('API Error', [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'status_code' => $statusCode,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'context' => $context,
        ]);
    }

    /**
     * Log validation errors
     */
    public static function logValidationError(string $action, array $errors)
    {
        Log::channel('api')->warning('API Validation Error', [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'errors' => $errors,
        ]);
    }

    /**
     * Log device control action (relay toggle, etc)
     */
    public static function logDeviceControl(string $action, int $deviceId, array $controlData)
    {
        Log::channel('api')->info('Device Control Action', [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'device_id' => $deviceId,
            'control_data' => $controlData,
        ]);
    }

    /**
     * Sanitize input untuk logging (remove sensitive data)
     */
    private static function sanitizeInput(array $input): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($input[$key])) {
                $input[$key] = '***REDACTED***';
            }
        }
        
        return $input;
    }

    /**
     * Log database query performance
     */
    public static function logQueryPerformance(string $query, array $bindings, float $duration)
    {
        if ($duration > 1000) { // Log hanya query yang > 1 detik
            Log::channel('api')->warning('Slow Database Query', [
                'timestamp' => now()->toIso8601String(),
                'query' => $query,
                'bindings' => $bindings,
                'duration_ms' => round($duration, 2),
            ]);
        }
    }

    /**
     * Log device connection status change
     */
    public static function logDeviceStatusChange(int $deviceId, string $oldStatus, string $newStatus)
    {
        Log::channel('api')->info('Device Status Changed', [
            'timestamp' => now()->toIso8601String(),
            'device_id' => $deviceId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }
}
