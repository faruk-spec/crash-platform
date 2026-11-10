<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Device;
use App\Models\GameRound;

Route::get('/', function () {
    return redirect('/admin/dashboard');
});

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $dbConnected = false;
        $dbMigrated = false;
        $dbError = null;

        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $dbMigrated = Schema::hasTable('game_rounds') && Schema::hasTable('devices');
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        $totalRounds = 0;
        $latestMultiplier = '1.00x';
        $subTwoStreak = 0;
        $activeDevices = 0;

        if ($dbMigrated) {
            try {
                $totalRounds = GameRound::count();
                $recent = GameRound::orderBy('id', 'desc')->take(10)->get();
                $latestMultiplier = $recent->first()?->crash_multiplier ? number_format($recent->first()->crash_multiplier, 2) . 'x' : '1.00x';
                foreach ($recent as $r) {
                    if ($r->crash_multiplier < 2.0) $subTwoStreak++;
                    else break;
                }
                $activeDevices = Device::where('last_seen_at', '>=', now()->subMinutes(15))->count();
            } catch (\Throwable $e) {
                // Keep default zeros
            }
        }

        return view('admin.dashboard', compact(
            'totalRounds',
            'latestMultiplier',
            'subTwoStreak',
            'activeDevices',
            'dbConnected',
            'dbMigrated',
            'dbError'
        ));
    });

    Route::get('/devices', function () {
        $devices = [];
        try {
            if (Schema::hasTable('devices')) {
                $devices = Device::with('configuration')->get();
            }
        } catch (\Throwable $e) {
            $devices = [];
        }
        return view('admin.devices', compact('devices'));
    });

    Route::get('/rounds', function () {
        $rounds = collect();
        try {
            if (Schema::hasTable('game_rounds')) {
                $rounds = GameRound::orderBy('id', 'desc')->paginate(25);
            }
        } catch (\Throwable $e) {
            $rounds = collect();
        }
        return view('admin.rounds', compact('rounds'));
    });

    Route::get('/activation', function () {
        return redirect('/admin/devices');
    });

    Route::get('/github', function () {
        return view('admin.github');
    });

    Route::get('/models', function () {
        return redirect('/admin/dashboard');
    });

    Route::post('/github/trigger-build', function () {
        $owner = env('GITHUB_REPO_OWNER', 'faruk-spec');
        $repo = env('GITHUB_REPO_NAME', 'crash-platform');
        $token = env('GITHUB_PERSONAL_ACCESS_TOKEN');

        if ($token) {
            \Illuminate\Support\Facades\Http::withToken($token)
                ->post("https://api.github.com/repos/{$owner}/{$repo}/actions/workflows/android-ci.yml/dispatches", [
                    'ref' => 'main',
                    'inputs' => ['release_type' => 'release']
                ]);
        }

        return redirect('/admin/github')->with('message', 'Cloud build triggered successfully!');
    });
});
