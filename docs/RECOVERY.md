# System Recovery & Persistence Runbook

## Restart Persistence Guarantees

### 1. Android Device Restart / Battery Loss
* **Room Database (WAL)**: All recorded rounds and inspect logs remain durable on disk across reboots.
* **BootReceiver**: Receives `android.intent.action.BOOT_COMPLETED` and immediately reschedules `BatchSyncWorker`.
* **Pending Uploads**: Un-synced records automatically resume upload as soon as network connectivity is re-established.

### 2. VPS Backend / Apache Restart
* **Atomic Releases**: Active release pointer `current` remains valid during server reboots.
* **Database State**: MySQL InnoDB transactions guarantee zero data loss.
* **Horizon Queues**: Interrupted background jobs are retried with exponential backoff.

### 3. VPS Rollback Procedure
If a bad release is deployed, roll back to the previous release in seconds:
```bash
cd /www/wwwroot/crash-platform
PREV_RELEASE=$(ls -td releases/* | sed -n '2p')
ln -nfs "$PREV_RELEASE" current
systemctl reload php-fpm-83 || systemctl reload apache2
```
