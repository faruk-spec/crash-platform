# Web Control Center & Administration Dashboard

## Architecture
* Built with Laravel 11 Blade templates, Tailwind CSS, Alpine.js, and Chart.js.
* Zero external UI dependencies; fully responsive for mobile and desktop displays.

## Core Modules
1. **Overview Dashboard (`/admin/dashboard`)**:
   * Total recorded rounds counter.
   * Latest observed crash multiplier with status badge.
   * Real-time consecutive sub-2.0x crash streak counter.
   * Interactive Chart.js graph rendering multipliers vs pre-crash animation jerk index.
2. **Android Device Manager (`/admin/devices`)**:
   * Real-time list of registered collector devices.
   * Battery level, RAM usage, and online/offline status indicator.
   * Global Predictor Activation Control panel (pushing thresholds to devices).
3. **Inspect & Round History (`/admin/rounds`)**:
   * Full tabular log of completed rounds.
   * Inspect modal showing raw WebSocket frames and console errors for any round.
4. **GitHub CI/CD Manager (`/admin/github`)**:
   * Central repository status.
   * One-click "Trigger Android Cloud Build" button.
   * In-app distribution release tracking with SHA-256 verification.
