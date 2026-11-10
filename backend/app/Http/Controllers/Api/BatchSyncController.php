<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use App\Models\GameRound;
use App\Models\RawObservation;
use App\Models\SyncBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BatchSyncController {
    public function sync(Request $request): JsonResponse {
        $validated = $request->validate([
            'batch_uuid' => 'required|uuid',
            'device_uuid' => 'required|uuid',
            'records' => 'required|array',
            'checksum' => 'required|string|size:64'
        ]);

        $device = Device::where('device_uuid', $validated['device_uuid'])->first();
        if (!$device) {
            return response()->json(['error' => 'Device not registered'], 401);
        }

        // Idempotency check: if batch already processed, return success
        $existingBatch = SyncBatch::where('batch_uuid', $validated['batch_uuid'])->first();
        if ($existingBatch && $existingBatch->status === 'PROCESSED') {
            return response()->json([
                'status' => 'SUCCESS',
                'batch_uuid' => $validated['batch_uuid'],
                'message' => 'Batch already processed idempotently',
                'processed_count' => $existingBatch->record_count
            ], 200);
        }

        $processedCount = 0;

        DB::beginTransaction();
        try {
            $batch = SyncBatch::firstOrCreate(
                ['batch_uuid' => $validated['batch_uuid']],
                [
                    'device_id' => $device->id,
                    'record_count' => count($validated['records']),
                    'checksum' => $validated['checksum'],
                    'status' => 'RECEIVED',
                    'created_at' => now()
                ]
            );

            foreach ($validated['records'] as $record) {
                // 1. Process Completed Game Round if present
                if (isset($record['crash_multiplier']) && isset($record['started_at'])) {
                    $round = GameRound::firstOrCreate(
                        ['round_uuid' => $record['round_uuid'] ?? (string) Str::uuid()],
                        [
                            'server_round_id' => $record['server_round_id'] ?? null,
                            'crash_multiplier' => $record['crash_multiplier'],
                            'started_at' => $record['started_at'],
                            'crashed_at' => $record['crashed_at'] ?? now(),
                            'duration_ms' => $record['duration_ms'] ?? 0,
                            'provably_fair_hash' => $record['provably_fair_hash'] ?? null,
                            'animation_metrics' => $record['animation_metrics'] ?? null,
                            'status' => 'VALID'
                        ]
                    );

                    // Attach raw observation if payload attached
                    if (isset($record['raw_inspect'])) {
                        RawObservation::create([
                            'observation_uuid' => (string) Str::uuid(),
                            'device_id' => $device->id,
                            'round_id' => $round->id,
                            'source_type' => 'WEB_INSPECT',
                            'raw_payload' => $record['raw_inspect'],
                            'captured_at' => $record['started_at'],
                            'created_at' => now()
                        ]);
                    }
                } elseif (isset($record['raw_payload'])) {
                    // Raw observation without finalized round
                    RawObservation::create([
                        'observation_uuid' => $record['observation_uuid'] ?? (string) Str::uuid(),
                        'device_id' => $device->id,
                        'round_id' => null,
                        'source_type' => $record['source_type'] ?? 'WEB_INSPECT',
                        'raw_payload' => $record['raw_payload'],
                        'captured_at' => $record['captured_at'] ?? now(),
                        'created_at' => now()
                    ]);
                }

                $processedCount++;
            }

            $batch->update(['status' => 'PROCESSED']);
            $device->update(['last_seen_at' => now()]);

            DB::commit();

            return response()->json([
                'status' => 'SUCCESS',
                'batch_uuid' => $validated['batch_uuid'],
                'processed_count' => $processedCount,
                'config_version' => $device->configuration?->config_version ?? 1,
                'server_time' => now()->toIso8601String()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'FAILED',
                'batch_uuid' => $validated['batch_uuid'],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
