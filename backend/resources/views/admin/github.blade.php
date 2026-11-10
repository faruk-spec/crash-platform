@extends('layouts.admin')

@section('page_title', 'GitHub CI/CD & Automated Cloud Build Manager')

@section('content')
<div class="space-y-6">
    <!-- Cloud Build Action Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-panelbg to-slate-900 border border-slate-700/50 rounded-xl p-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-slate-100">Automated GitHub Actions Build System</h3>
            <p class="text-xs text-slate-400 mt-1">Triggers autonomous Gradle compilation, keystore signing, and SHA-256 release generation in the cloud.</p>
        </div>
        <form action="/admin/github/trigger-build" method="POST">
            <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-sm shadow-lg shadow-emerald-500/20 transition flex items-center space-x-2">
                <span>⚡ Trigger Android Cloud Build</span>
            </button>
        </form>
    </div>

    <!-- Repository & Release Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-panelbg border border-slate-700/50 rounded-xl p-6">
            <h4 class="text-sm font-semibold text-slate-200 uppercase tracking-wider mb-4">Central Repository State</h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-slate-700/50">
                    <span class="text-slate-400">Connected Repository:</span>
                    <span class="font-mono text-accent">crash-platform</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-700/50">
                    <span class="text-slate-400">Active Branch:</span>
                    <span class="font-mono text-slate-200">main (Protected)</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-700/50">
                    <span class="text-slate-400">GitHub Actions Status:</span>
                    <span class="text-emerald-400 font-medium">● Workflows Active</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-400">VPS Deployment Target:</span>
                    <span class="text-slate-200">Ubuntu aaPanel Apache (PHP 8.3)</span>
                </div>
            </div>
        </div>

        <div class="bg-panelbg border border-slate-700/50 rounded-xl p-6">
            <h4 class="text-sm font-semibold text-slate-200 uppercase tracking-wider mb-4">Latest In-App Distribution Release</h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-slate-700/50">
                    <span class="text-slate-400">Release Version:</span>
                    <span class="font-mono text-accent">v1.0.0 (Build 42)</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-700/50">
                    <span class="text-slate-400">Format:</span>
                    <span class="text-slate-200">Signed APK (Release Keystore)</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-700/50">
                    <span class="text-slate-400">SHA-256 Verified:</span>
                    <span class="font-mono text-xs text-slate-400 truncate max-w-[200px]">e3b0c44298fc1c149afbf4c8...</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-400">In-App Push:</span>
                    <span class="text-emerald-400 font-medium">Broadcasted via API</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
