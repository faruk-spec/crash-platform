# Changelog

All notable changes to this platform are documented in this file.

## [1.0.0] - 2026-11-10
### Added
- **Monorepo Architecture**: Initialized unified codebase containing Android app, Laravel backend, Web Control Center, and Python ML engine.
- **In-App Harvester**: Complete `injected_harvester.js` hooking WebSockets, network XHR/Fetch, console logs, and 60FPS Canvas plane coordinates and bezier spline curvature $\kappa$.
- **Android Client**:
  - Jetpack Compose dual-layer UI with embedded game browser and floating predictor HUD.
  - Room SQLite in WAL mode for persistent offline round buffering.
  - WorkManager idempotent batch synchronization with exponential backoff.
  - In-App Automated Update Manager with SHA-256 verification and native package installer.
- **Laravel Backend**:
  - Normalized 25-table MySQL database migration suite.
  - RESTful versioned API (`/api/v1/devices`, `/api/v1/sync/batch`, `/api/v1/config`, `/api/v1/releases`).
  - Remote Predictor Activation Control interface.
- **Web Control Center**:
  - Blade + Tailwind CSS responsive dashboard.
  - Interactive Chart.js telemetry visualizer comparing multipliers to pre-crash animation jerk index.
  - GitHub CI/CD Manager with one-click "Trigger Android Cloud Build" button.
- **AI/ML & Vision Engine**:
  - Kaplan-Meier non-parametric survival estimator.
  - LightGBM classifier for target multiplier survival probability.
  - OpenCV spline curvature and frame delta jitter extractor.
  - Zero paid AI dependencies.
- **GitHub Actions Automation**:
  - Automated cloud Android APK/AAB compilation and signing (`android-ci.yml`).
  - Zero-downtime aaPanel VPS deployment with atomic symlinking (`backend-deploy.yml`).
- **Documentation Suite**: Complete set of 15 architectural and operational documents.
