<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autonomous Crash Game Control Center')</title>
    <!-- Tailwind CSS CDN for instant responsive styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkbg: '#0F172A',
                        panelbg: '#1E293B',
                        accent: '#38BDF8',
                        danger: '#EF4444',
                        success: '#10B981',
                        warning: '#F59E0B'
                    }
                }
            }
        }
    </script>
    <!-- Chart.js for real-time telemetry visualizers -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
    </style>
</head>
<body class="bg-darkbg text-slate-100 min-h-screen flex">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-panelbg border-r border-slate-700/50 flex flex-col justify-between">
        <div>
            <div class="p-6 border-b border-slate-700/50 flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-accent/20 border border-accent flex items-center justify-center text-accent font-bold">⚡</div>
                <div>
                    <h1 class="text-sm font-bold uppercase tracking-wider text-slate-200">Crash Control</h1>
                    <span class="text-xs text-accent font-mono">v1.0.0-PROD</span>
                </div>
            </div>
            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 text-slate-200 transition">
                    📊 Overview Dashboard
                </a>
                <a href="/admin/devices" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 text-slate-300 transition">
                    📱 Android Device Manager
                </a>
                <a href="/admin/rounds" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 text-slate-300 transition">
                    🔍 Inspect & Round History
                </a>
                <a href="/admin/activation" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 text-slate-300 transition">
                    🎯 Predictor Activation Control
                </a>
                <a href="/admin/github" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 text-slate-300 transition">
                    🐙 GitHub CI/CD & Cloud Build
                </a>
                <a href="/admin/models" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-slate-700/50 text-slate-300 transition">
                    🧠 AI/ML & Survival Engine
                </a>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-700/50">
            <div class="flex items-center space-x-2 text-xs text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full bg-success animate-pulse"></span>
                <span>System Status: Autonomous</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="h-16 bg-panelbg/60 backdrop-blur border-b border-slate-700/50 px-8 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-100">@yield('page_title', 'Control Center')</h2>
            <div class="flex items-center space-x-4">
                <span class="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700">Target: damansuperstar1.com</span>
                <span class="text-xs bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full border border-emerald-500/20 font-mono">Sync Active</span>
            </div>
        </header>

        <section class="p-8 space-y-6">
            @yield('content')
        </section>
    </main>
</body>
</html>

