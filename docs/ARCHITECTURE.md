# System Architecture Document

## High-Level Topology

```
[ Edge: Android In-App Browser ]
       │  (Injected Harvester)
       ├── WebSocket Frames (Ticks, Multipliers, End Packets)
       ├── Canvas 60FPS Plane Coordinates (x, y) & Jerk Index
       └── Console & Network Diagnostics
       │
       ▼
[ Room Database (WAL Mode) ]
       │
       ▼
[ WorkManager Sync Engine ] ──(HTTPS Idempotent Batch)──▶ [ Laravel 11 Gateway ]
                                                                 │
                                                    ┌────────────┴────────────┐
                                                    ▼                         ▼
                                             [ MySQL 8.0 ]             [ Web Control ]
                                                    │
                                                    ▼
                                           [ ML/CV Microservice ]
                                        (Survival Models + OpenCV)
```

## Key Architectural Invariants
1. **Offline Tolerance**: Every observation and completed round is written to local SQLite WAL before network dispatch.
2. **Idempotency**: All batch synchronization requests carry a client-generated UUID and SHA-256 payload checksum.
3. **No Paid APIs**: The entire predictive and vision stack runs self-hosted on your own hardware/VPS.
4. **Zero-Downtime Deployments**: Deployments to Ubuntu aaPanel VPS utilize atomic symlink switching (`current -> releases/TIMESTAMP`).

