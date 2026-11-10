# Data Collection Specification

## In-App Harvester Ingestion Engine

Data collection is performed by `injected_harvester.js` running inside the Android app's hardened `WebView`.

### Captured Streams

1. **WebSocket Packet Stream**:
   * Raw JSON payloads received from the upstream game server (`round_start`, `tick`, `crash`).
   * Packet arrival intervals $\Delta t_{pkt}$ measured with microsecond accuracy.
2. **Canvas 60FPS Animation Metrics**:
   * Plane coordinates $(x_t, y_t)$ intercepted via `ctx.drawImage`.
   * Plane velocity vector $\vec{v}(t)$ and acceleration jerk $\vec{j}(t)$.
   * Flight trail bezier spline curvature $\kappa(s)$ intercepted via `ctx.bezierCurveTo`.
3. **Browser Inspect Data**:
   * Full console logs (`console.log`, `console.warn`, `console.error`).
   * Network request headers and response timings.

### Storage & Transmission
* Every round and observation is stored locally in SQLite (`game_rounds`, `raw_observations`).
* Batches are synchronized via Android `WorkManager` using idempotent HTTP POST requests with SHA-256 payload checksums.
