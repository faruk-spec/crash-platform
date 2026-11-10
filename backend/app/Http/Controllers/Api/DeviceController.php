<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use App\Models\DeviceConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController {
    public function register(Request $request): JsonResponse {
        $validated = $request->validate([
            'device_uuid' => 'required|uuid',
            'device_name' => 'required|string|max:100',
            'hardware_model' => 'required|string|max:100',
            'os_version' => 'required|string|max:50',
            'app_version' => 'required|string|max:50'
        ]);

        $device = Device::firstOrNew(['device_uuid' => $validated['device_uuid']]);
        $device->device_name = $validated['device_name'];
        $device->hardware_model = $validated['hardware_model'];
        $device->os_version = $validated['os_version'];
        $device->app_version = $validated['app_version'];
        if (!$device->auth_token) {
            $device->auth_token = Str::random(60);
        }
        $device->last_seen_at = now();
        $device->save();

        // Ensure default configuration exists
        if (!$device->configuration) {
            DeviceConfiguration::create([
                'device_id' => $device->id,
                'config_version' => 1,
                'min_multiplier' => 1.00,
                'max_multiplier' => 100.00,
                'activation_mode' => 'BEFORE_5X',
                'min_confidence' => 0.75,
                'collection_enabled' => true,
                'predictor_enabled' => true,
                'sync_interval_seconds' => 30
            ]);
        }

        return response()->json([
            'status' => 'SUCCESS',
            'device_id' => $device->id,
            'auth_token' => $device->auth_token,
            'config' => $device->configuration
        ], 200);
    }

    public function heartbeat(Request $request): JsonResponse {
        $validated = $request->validate([
            'device_uuid' => 'required|uuid',
            'battery_level' => 'required|integer|min:0|max:100',
            'is_charging' => 'required|boolean',
            'memory_usage_mb' => 'required|integer',
            'network_type' => 'required|string',
            'pending_sync_count' => 'required|integer'
        ]);

        $device = Device::where('device_uuid', $validated['device_uuid'])->firstOrFail();
        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'PONG',
            'server_time' => now()->toIso8601String(),
            'config_version' => $device->configuration?->config_version ?? 1
        ], 200);
    }
}

