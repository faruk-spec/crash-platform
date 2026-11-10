<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use App\Models\DeviceConfiguration;
use App\Models\ConfigurationVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController {
    public function getCurrent(Request $request): JsonResponse {
        $deviceUuid = $request->header('X-Device-UUID');
        $device = Device::where('device_uuid', $deviceUuid)->first();

        $config = $device?->configuration ?? DeviceConfiguration::whereNull('device_id')->first();
        if (!$config) {
            $config = DeviceConfiguration::create([
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

        return response()->json($config, 200);
    }

    public function updateRemoteConfig(Request $request): JsonResponse {
        $validated = $request->validate([
            'device_id' => 'nullable|integer',
            'activation_mode' => 'required|in:ALL,BEFORE_2X,BEFORE_5X,BEFORE_8X,AFTER_8X,CUSTOM',
            'min_multiplier' => 'required|numeric|min:1.0',
            'max_multiplier' => 'required|numeric|gt:min_multiplier',
            'custom_min' => 'nullable|numeric',
            'custom_max' => 'nullable|numeric',
            'min_confidence' => 'required|numeric|between:0,1',
            'collection_enabled' => 'required|boolean',
            'predictor_enabled' => 'required|boolean',
            'sync_interval_seconds' => 'required|integer|min:5|max:3600',
            'active_model_version' => 'required|string|max:50'
        ]);

        $query = DeviceConfiguration::query();
        if (!empty($validated['device_id'])) {
            $query->where('device_id', $validated['device_id']);
        } else {
            $query->whereNull('device_id');
        }

        $config = $query->firstOrNew();
        $config->fill($validated);
        $config->config_version = ($config->config_version ?? 0) + 1;
        $config->save();

        // Save immutable audit snapshot
        ConfigurationVersion::create([
            'version_number' => $config->config_version,
            'config_payload' => $config->toArray(),
            'created_by' => auth()->id() ?? null,
            'created_at' => now()
        ]);

        return response()->json([
            'status' => 'UPDATED',
            'config_version' => $config->config_version,
            'config' => $config
        ], 200);
    }
}

