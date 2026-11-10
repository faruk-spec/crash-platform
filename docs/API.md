# REST API Specification (Version 1)

All endpoints accept and return `application/json`, require HTTPS in production, and enforce rate-limiting.

## Endpoints

### 1. Device Management
* `POST /api/v1/devices/register`: Registers hardware UUID and provisions auth token.
* `POST /api/v1/devices/heartbeat`: Transmits battery, RAM, network type, and un-synced count.

### 2. Synchronization & Ingestion
* `POST /api/v1/sync/batch`: Idempotent batch upload of completed rounds and raw inspect packets.
  * Required headers: `X-Device-UUID`
  * Body: `batch_uuid`, `device_uuid`, `records`, `checksum`

### 3. Remote Activation Control
* `GET /api/v1/config/current`: Returns active predictor thresholds (e.g. `BEFORE_5X`, `min_confidence`).
* `POST /api/v1/admin/config/update`: Centrally modifies predictor activation thresholds.

### 4. In-App Releases & GitHub Webhooks
* `GET /api/v1/releases/latest?version_code=XYZ`: In-app update check endpoint.
* `POST /api/v1/github/build-complete`: Webhook from GitHub Actions registering newly compiled signed APKs.

### 5. Round Analytics
* `GET /api/v1/rounds/history`: Paginated list of historical crash rounds.
* `GET /api/v1/analytics/realtime-summary`: Real-time crash statistics and current sub-2.0x streak.

