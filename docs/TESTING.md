# Testing Strategy & Acceptance Criteria

## Automated Test Suites

### 1. Android Client Tests
* **Unit Tests (`testReleaseUnitTest`)**:
  * Room DAO insertion, retrieval, and batch grouping.
  * Checksum verification algorithm.
  * UpdateManager version comparison logic.
* **Instrumentation Tests**:
  * WebView injection and JavaScript-to-Kotlin bridge messaging.

### 2. Backend Integration Tests (PHPUnit)
* **API Endpoints**:
  * Device registration idempotency.
  * Batch synchronization with simulated duplicate payloads.
  * Config update audit logging.
  * Webhook secret rejection.

### 3. ML Model Validation
* **Dataset Integrity**:
  * Verify absence of NaN or infinite multipliers.
  * Chronological monotonic timestamp validation.
* **Brier Score Baseline**:
  * Model must outperform random uniform coin-flip baseline ($BS \le 0.25$).
