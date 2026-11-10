@extends('layouts.admin')

@section('page_title', 'Historical Rounds & Raw Inspect Explorer')

@section('content')
<div class="space-y-6">
    <div class="bg-panelbg border border-slate-700/50 rounded-xl overflow-hidden">
        <div class="p-6 border-b border-slate-700/50 flex justify-between items-center">
            <div>
                <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Crash Game Observations</h3>
                <p class="text-xs text-slate-400 mt-1">Ground-truth recorded rounds with pre-crash animation metrics and WebSocket frames.</p>
            </div>
            <span class="text-xs bg-slate-800 text-accent px-3 py-1 rounded-full border border-slate-700 font-mono">
                {{ method_exists($rounds, 'total') ? $rounds->total() : count($rounds) }} Rounds Recorded
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/50 text-xs uppercase text-slate-400 border-b border-slate-700/50">
                    <tr>
                        <th class="px-6 py-3">Round ID</th>
                        <th class="px-6 py-3">Final Multiplier</th>
                        <th class="px-6 py-3">Flight Duration</th>
                        <th class="px-6 py-3">Animation Metrics</th>
                        <th class="px-6 py-3">Observed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($rounds as $round)
                    <tr>
                        <td class="px-6 py-4 font-mono font-medium text-slate-100">
                            {{ $round->server_round_id ?? substr($round->round_uuid, 0, 8) }}
                        </td>
                        <td class="px-6 py-4 font-mono font-bold {{ $round->crash_multiplier < 2.0 ? 'text-danger' : ($round->crash_multiplier < 5.0 ? 'text-accent' : 'text-emerald-400') }}">
                            {{ number_format($round->crash_multiplier, 2) }}x
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">
                            {{ number_format($round->duration_ms / 1000, 2) }}s
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-300">
                            <span class="px-2 py-0.5 rounded bg-slate-800 font-mono text-amber-400 border border-slate-700">
                                Jerk: {{ isset($round->animation_metrics['avg_jerk']) ? number_format($round->animation_metrics['avg_jerk'], 4) : '0.0120' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">
                            {{ $round->started_at ? $round->started_at->diffForHumans() : 'Recently' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                            No rounds recorded yet. Connect an Android device with the in-app harvester to start collecting telemetry.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
