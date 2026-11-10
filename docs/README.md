# Autonomous Crash Game Analysis & Control Platform

A production-grade, distributed engineering platform designed to investigate, collect, analyze, and monitor crash games (Aviator style) on `https://damansuperstar1.com/`.

## Architecture Overview

1. **Android Native Client (`android/`)**: Hardened in-app browser with injected WebSocket and Canvas 2D/WebGL harvester, Room SQLite WAL offline persistence, WorkManager background synchronization, and automatic in-app APK update manager.
2. **Central Backend (`backend/`)**: Laravel 11.x REST API, 23-table MySQL normalized database, remote activation control manager, and in-app release distribution.
3. **Web Control Center (`web/` & `backend/resources/views/`)**: Blade + Tailwind CSS dashboard for live multiplier tracking, pre-crash animation jitter inspection, device management, and GitHub Actions cloud build triggering.
4. **AI/ML & Vision Engine (`ml_service/`)**: Empirical survival analysis (Kaplan-Meier, LightGBM, OpenCV spline curvature analysis) using 100% free, self-hosted, open-source libraries. Zero paid AI APIs.
5. **CI/CD Platform (`.github/workflows/`)**: GitHub Actions workflows for automated cloud Android APK/AAB compilation with signing and zero-downtime aaPanel VPS deployment.

## Repository Structure

```
crash_game_platform/
├── android/          # Native Kotlin Compose Android App
├── backend/          # Laravel 11 REST API & Control Center
├── ml_service/       # Python Statistical Survival & OpenCV Engine
├── .github/          # GitHub Actions CI/CD Pipelines
└── docs/             # Complete 15 Architectural Documents
```

