<?php

use Illuminate\Support\Facades\Route;
use App\Models\Device;
use App\Models\GameRound;

Route::get('/', function () {
    return redirect('/admin/dashboard');
});

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $totalRounds = GameRound::count();
        $recent = GameRound::orderBy('id', 'desc')->take(10)->get();
        $latestMultiplier = $recent->first()?->crash_multiplier ? number_format($recent->first()->crash_multiplier, 2) . 'x' : '1.00x';
        $subTwoStreak = 0;
        foreach ($recent as $r) {
            if ($r->crash_multiplier < 2.0) $subTwoStreak++;
            else break;
        }
        $activeDevices = Device::where('last_seen_at', '>=', now()->subMinutes(15))->count();

        return view('admin.dashboard', compact('totalRounds', 'latestMultiplier', 'subTwoStreak', 'activeDevices'));
    });

    Route::get('/devices', function () {
        return view('admin.devices');
    });

    Route::get('/rounds', function () {
        $rounds = GameRound::orderBy('id', 'desc')->paginate(25);
        return view('admin.rounds', compact('rounds'));
    });

    Route::get('/activation', function () {
        return view('admin.devices');
    });

    Route::get('/github', function () {
        return view('admin.github');
    });

    Route::get('/models', function () {
        return view('admin.dashboard');
    });

    Route::post('/github/trigger-build', function () {
        // Triggers GitHub Actions workflow_dispatch via GitHub REST API
        $owner = env('GITHUB_REPO_OWNER', 'your-org');
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

