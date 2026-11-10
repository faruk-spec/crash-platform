@extends('layouts.admin')

@section('page_title', 'Android Device Management & Remote Activation Control')

@section('content')
<div class="space-y-6">
    <!-- Quick Activation Control Panel -->
    <div class="bg-panelbg border border-slate-700/50 rounded-xl p-6">
        <h3 class="text-base font-semibold text-slate-100 mb-2">Global Predictor Activation Control</h3>
        <p class="text-xs text-slate-400 mb-6">Centrally controls the prediction activation thresholds pushed to all Android clients.</p>

        <form action="/api/v1/admin/config/update" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Activation Mode</label>
                <select name="activation_mode" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200">
                    <option value="BEFORE_2X">Activate Before 2.00x</option>
                    <option value="BEFORE_5X" selected>Activate Before 5.00x</option>
                    <option value="BEFORE_8X">Activate Before 8.00x</option>
                    <option value="AFTER_8X">Activate After 8.00x</option>
                    <option value="CUSTOM">Custom Multiplier Range</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Minimum Confidence (%)</label>
                <input type="number" step="0.01" name="min_confidence" value="0.75" min="0.50" max="0.99" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Sync Interval (Seconds)</label>
                <input type="number" name="sync_interval_seconds" value="30" min="5" max="300" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-accent hover:bg-sky-400 text-slate-900 font-semibold px-4 py-2 rounded-lg text-sm transition">
                    Push Config to Devices
                </button>
            </div>
        </form>
    </div>

    <!-- Connected Devices List -->
    <div class="bg-panelbg border border-slate-700/50 rounded-xl overflow-hidden">
        <div class="p-6 border-b border-slate-700/50 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-200 uppercase tracking-wider">Registered Android Devices</h3>
            <span class="text-xs text-slate-400">Automatic Heartbeat & In-App Sync</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/50 text-xs uppercase text-slate-400 border-b border-slate-700/50">
                    <tr>
                        <th class="px-6 py-3">Device Name</th>
                        <th class="px-6 py-3">Model & OS</th>
                        <th class="px-6 py-3">App Version</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Last Heartbeat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <tr>
                        <td class="px-6 py-4 font-mono font-medium text-slate-100">Samsung S24 Ultra (Collector-01)</td>
                        <td class="px-6 py-4 text-xs text-slate-400">SM-S928B (Android 14)</td>
                        <td class="px-6 py-4 font-mono text-accent">v1.0.0 (Build 42)</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">ONLINE</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">Just now</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

