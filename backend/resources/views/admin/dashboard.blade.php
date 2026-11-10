@extends('layouts.admin')

@section('page_title', 'Overview Analytics & Real-Time Telemetry')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-panelbg border border-slate-700/50 rounded-xl p-5 shadow-sm">
        <span class="text-xs uppercase font-semibold text-slate-400">Total Rounds Recorded</span>
        <div class="text-3xl font-bold text-accent mt-2 font-mono" id="stat-total-rounds">{{ $totalRounds ?? '0' }}</div>
        <span class="text-xs text-emerald-400 mt-2 block">↑ 100% Empirically Verified</span>
    </div>

    <div class="bg-panelbg border border-slate-700/50 rounded-xl p-5 shadow-sm">
        <span class="text-xs uppercase font-semibold text-slate-400">Latest Crash Multiplier</span>
        <div class="text-3xl font-bold text-danger mt-2 font-mono" id="stat-latest-mult">{{ $latestMultiplier ?? '1.00x' }}</div>
        <span class="text-xs text-slate-400 mt-2 block">Captured from In-App Harvester</span>
    </div>

    <div class="bg-panelbg border border-slate-700/50 rounded-xl p-5 shadow-sm">
        <span class="text-xs uppercase font-semibold text-slate-400">Sub-2.0x Streak</span>
        <div class="text-3xl font-bold text-warning mt-2 font-mono" id="stat-streak">{{ $subTwoStreak ?? '0' }}</div>
        <span class="text-xs text-slate-400 mt-2 block">Consecutive Low Crashes</span>
    </div>

    <div class="bg-panelbg border border-slate-700/50 rounded-xl p-5 shadow-sm">
        <span class="text-xs uppercase font-semibold text-slate-400">Active Android Devices</span>
        <div class="text-3xl font-bold text-success mt-2 font-mono" id="stat-devices">{{ $activeDevices ?? '0' }}</div>
        <span class="text-xs text-slate-400 mt-2 block">Connected & Ingesting</span>
    </div>
</div>

<!-- Real-time Chart of Crash Multipliers and Animation Jitter -->
<div class="bg-panelbg border border-slate-700/50 rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Historical Multipliers & Pre-Crash Animation Jitter</h3>
        <span class="text-xs text-slate-400 font-mono">Live 60FPS Harvester Telemetry</span>
    </div>
    <div class="h-80">
        <canvas id="telemetryChart"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('telemetryChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['R-10', 'R-9', 'R-8', 'R-7', 'R-6', 'R-5', 'R-4', 'R-3', 'R-2', 'R-1 (Latest)'],
            datasets: [
                {
                    label: 'Crash Multiplier',
                    data: [1.24, 3.45, 1.05, 12.80, 2.10, 1.15, 1.90, 4.30, 1.02, 2.45],
                    borderColor: '#38BDF8',
                    backgroundColor: 'rgba(56, 189, 248, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Animation Jerk Index (Pre-Crash Jitter)',
                    data: [0.012, 0.015, 0.089, 0.014, 0.022, 0.095, 0.034, 0.018, 0.112, 0.025],
                    borderColor: '#F59E0B',
                    borderWidth: 1.5,
                    borderDash: [5, 5],
                    yAxisID: 'y1',
                    tension: 0.2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94A3B8' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: '#F59E0B' }
                },
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94A3B8' }
                }
            },
            plugins: {
                legend: { labels: { color: '#E2E8F0' } }
            }
        }
    });
</script>
@endsection

