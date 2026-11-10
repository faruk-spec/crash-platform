<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\BatchSyncController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\ReleaseController;
use App\Models\GameRound;

Route::prefix('v1')->group(function () {
    // 1. Device Management
    Route::post('/devices/register', [DeviceController::class, 'register']);
    Route::post('/devices/heartbeat', [DeviceController::class, 'heartbeat']);

    // 2. Data Synchronization & Ingestion
    Route::post('/sync/batch', [BatchSyncController::class, 'sync']);

    // 3. Predictor Activation Control & Configuration
    Route::get('/config/current', [ConfigController::class, 'getCurrent']);
    Route::post('/admin/config/update', [ConfigController::class, 'updateRemoteConfig']);

    // 4. In-App Auto Updates & GitHub CI/CD Releases
    Route::get('/releases/latest', [ReleaseController::class, 'getLatest']);
    Route::post('/github/build-complete', [ReleaseController::class, 'onGitHubBuildComplete']);

    // 5. Game Rounds & Analytics Queries
    Route::get('/rounds/history', function () {
        return response()->json(
            GameRound::orderBy('id', 'desc')->paginate(50)
        );
    });

    Route::get('/analytics/realtime-summary', function () {
        $recent = GameRound::orderBy('id', 'desc')->take(100)->get();
        $totalRounds = GameRound::count();
        $avgMultiplier = $totalRounds > 0 ? (float) GameRound::avg('crash_multiplier') : 1.0;
        $subTwoStreak = 0;
        foreach ($recent as $r) {
            if ($r->crash_multiplier < 2.0) $subTwoStreak++;
            else break;
        }

        return response()->json([
            'total_rounds' => $totalRounds,
            'recent_sample_size' => $recent->count(),
            'average_multiplier' => round($avgMultiplier, 2),
            'current_sub_two_streak' => $subTwoStreak,
            'latest_multiplier' => $recent->first()?->crash_multiplier ?? 1.0
        ]);
    });
});

