# Automated Data & Model Processing Pipeline

```
[ Stage 1: Edge Collection ]
Android app captures WebSocket frames, canvas jerk, and completed crash multipliers.

[ Stage 2: SQLite WAL Buffering ]
Stored locally in Room Database; immune to process crashes or network dropouts.

[ Stage 3: Batch Upload ]
WorkManager triggers idempotent HTTPS upload to /api/v1/sync/batch.

[ Stage 4: MySQL 8.0 Ingestion ]
Laravel verifies checksum, deduplicates, and commits round to game_rounds.

[ Stage 5: Feature Normalization ]
Recent streak lengths, mean jerk index, and curvature variance are calculated.

[ Stage 6: Empirical ML Training ]
Python microservice fits Kaplan-Meier survival curves and updates LightGBM regressors.

[ Stage 7: Backtest Validation ]
Candidate models are evaluated against chronological out-of-sample holdout sets.

[ Stage 8: Live Signal Distribution ]
Verified models push conditional survival probabilities to edge devices.
```
