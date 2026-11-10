# Native Android Application Architecture

## Technology Stack
* **Language**: Kotlin 1.9+
* **UI**: Jetpack Compose & Material 3
* **Local Persistence**: Room SQLite in Write-Ahead Logging (WAL) mode
* **Background Scheduling**: WorkManager
* **Network**: Retrofit 2 & OkHttp 4
* **Distribution**: In-App PackageInstaller & FileProvider

## Component Responsibilities

### 1. In-App Game Browser (`WebView` + `injected_harvester.js`)
* Hardened, hardware-accelerated container rendering `https://damansuperstar1.com/`.
* Injected JavaScript hooks `WebSocket`, `console`, and `HTMLCanvasElement.getContext('2d')`.
* Real-time calculation of animation velocity $\vec{v}$, acceleration jerk $\vec{j}$, and spline curvature $\kappa$.

### 2. Dual-Layer Floating HUD
* Renders real-time telemetry over the browser.
* Displays current multiplier, animation jerk, spline curvature, and live empirical probability signals.

### 3. In-App Auto-Update Manager (`AppUpdateManager.kt`)
* Checks backend release registry on launch and via push.
* Downloads signed APK directly to app-private cache.
* Verifies SHA-256 cryptographic signature.
* Triggers native Android installation without requiring Google Play.

