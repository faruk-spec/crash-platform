# GitHub CI/CD & Cloud Build System

The platform utilizes GitHub Actions for continuous integration, automated Android compilation, and zero-downtime VPS deployment.

## Workflows

### 1. Automated Android Build & Cloud Release (`.github/workflows/android-ci.yml`)
* **Triggers**:
  * Git push to `main` branch.
  * Git release tag (`v*`).
  * Manual trigger via Web Dashboard ("Trigger Android Cloud Build" button).
* **Execution Steps**:
  1. Spins up an Ubuntu runner with Java 17.
  2. Runs Android lint and unit tests.
  3. Decodes release keystore from GitHub Secret (`ANDROID_KEYSTORE_BASE64`).
  4. Compiles signed APK using Gradle (`assembleRelease`).
  5. Computes SHA-256 cryptographic checksum.
  6. Publishes artifact to GitHub Releases.
  7. Calls Laravel backend webhook (`POST /api/v1/github/build-complete`), attaching the download URL and checksum.

### 2. VPS Zero-Downtime Deployment (`.github/workflows/backend-deploy.yml`)
* **Target**: Ubuntu VPS managed via aaPanel running Apache and PHP 8.3.
* **Process**:
  1. Installs Composer dependencies with autoloader optimization.
  2. Connects via SSH to VPS.
  3. Copies release to `/www/wwwroot/crash-platform/releases/TIMESTAMP/`.
  4. Links persistent `.env` and `storage/` directory.
  5. Executes database migrations (`php artisan migrate --force`).
  6. Executes atomic symlink switch (`current -> releases/TIMESTAMP`).
  7. Reloads PHP 8.3 FPM and restarts Horizon queues.

