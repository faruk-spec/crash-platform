<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('guard_name', 50)->default('web');
            $table->timestamps();
        });

        // 3. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('guard_name', 50)->default('web');
            $table->timestamps();
        });

        // 4. User Roles Pivot
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // 5. Android Devices
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_uuid')->unique();
            $table->string('device_name', 100);
            $table->string('hardware_model', 100);
            $table->string('os_version', 50);
            $table->string('app_version', 50);
            $table->string('auth_token', 255);
            $table->enum('status', ['ACTIVE', 'SUSPENDED', 'REVOKED'])->default('ACTIVE');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_seen_at']);
        });

        // 6. Device Tokens (FCM)
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->text('fcm_token');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Device Heartbeats
        Schema::create('device_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->unsignedTinyInteger('battery_level');
            $table->boolean('is_charging');
            $table->unsignedInteger('memory_usage_mb');
            $table->string('network_type', 20);
            $table->unsignedInteger('pending_sync_count');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['device_id', 'created_at']);
        });

        // 8. Device Configurations & Activation Control
        Schema::create('device_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->cascadeOnDelete();
            $table->unsignedInteger('config_version')->default(1);
            $table->decimal('min_multiplier', 8, 2)->default(1.00);
            $table->decimal('max_multiplier', 8, 2)->default(100.00);
            $table->enum('activation_mode', ['ALL', 'BEFORE_2X', 'BEFORE_5X', 'BEFORE_8X', 'AFTER_8X', 'CUSTOM'])->default('ALL');
            $table->decimal('custom_min', 8, 2)->nullable();
            $table->decimal('custom_max', 8, 2)->nullable();
            $table->decimal('min_confidence', 5, 4)->default(0.7000);
            $table->boolean('collection_enabled')->default(true);
            $table->boolean('predictor_enabled')->default(true);
            $table->unsignedInteger('sync_interval_seconds')->default(30);
            $table->string('active_model_version', 50)->default('v1.0-survival-empirical');
            $table->timestamps();
        });

        // 9. Configuration Version History
        Schema::create('configuration_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('version_number')->unique();
            $table->json('config_payload');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        // 10. In-App Application Releases
        Schema::create('application_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version_name', 50);
            $table->unsignedInteger('version_code')->unique();
            $table->enum('release_type', ['DEBUG', 'RELEASE', 'BUNDLE'])->default('RELEASE');
            $table->string('download_url', 255);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->char('sha256_checksum', 64);
            $table->string('signing_key_id', 100)->default('release-key-01');
            $table->text('release_notes')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // 11. Game Rounds (Ground Truth)
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->uuid('round_uuid')->unique();
            $table->string('server_round_id', 100)->nullable()->index();
            $table->decimal('crash_multiplier', 8, 2)->index();
            $table->timestamp('started_at', 3);
            $table->timestamp('crashed_at', 3);
            $table->unsignedInteger('duration_ms');
            $table->string('provably_fair_hash', 255)->nullable();
            $table->json('animation_metrics')->nullable();
            $table->enum('status', ['VALID', 'ANOMALOUS', 'INCOMPLETE'])->default('VALID');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['crash_multiplier', 'started_at']);
        });

        // 12. Raw Inspect Observations & WebSockets
        Schema::create('raw_observations', function (Blueprint $table) {
            $table->id();
            $table->uuid('observation_uuid')->unique();
            $table->foreignId('device_id')->nullable()->constrained('devices')->cascadeOnDelete();
            $table->foreignId('round_id')->nullable()->constrained('game_rounds')->nullOnDelete();
            $table->string('source_type', 50); // WEB_INSPECT, WS_INBOUND, CANVAS
            $table->json('raw_payload');
            $table->timestamp('captured_at', 3);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['device_id', 'captured_at']);
        });

        // 13. Visual Features (Computer Vision & Spline Metrics)
        Schema::create('visual_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->foreignId('observation_id')->nullable()->constrained('raw_observations')->nullOnDelete();
            $table->enum('window_phase', ['EARLY', 'MID', 'PRE_2X', 'PRE_5X', 'PRE_8X', 'POST_8X']);
            $table->unsignedInteger('timestamp_offset_ms');
            $table->double('curvature_k');
            $table->double('tangent_angle_deg');
            $table->double('spline_residual_rmse');
            $table->double('optical_flow_magnitude');
            $table->double('trail_pixel_density');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['round_id', 'window_phase']);
        });

        // 14. Analysis Results
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->string('model_version', 50);
            $table->enum('signal_state', ['SIGNAL_ACTIVE', 'NO_SIGNAL', 'INSUFFICIENT_DATA']);
            $table->decimal('predicted_range_min', 8, 2)->nullable();
            $table->decimal('predicted_range_max', 8, 2)->nullable();
            $table->decimal('confidence_score', 5, 4);
            $table->double('p_value')->nullable();
            $table->unsignedInteger('sample_size_used');
            $table->json('feature_contributions')->nullable();
            $table->timestamp('analyzed_at', 3);
            $table->timestamp('created_at')->useCurrent();
        });

        // 15. Prediction Records
        Schema::create('prediction_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('prediction_uuid')->unique();
            $table->foreignId('round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->decimal('target_threshold', 8, 2);
            $table->decimal('signal_generated_at_multiplier', 8, 2);
            $table->decimal('estimated_survival_probability', 5, 4);
            $table->enum('status', ['PENDING_OUTCOME', 'EVALUATED'])->default('PENDING_OUTCOME');
            $table->timestamp('created_at')->useCurrent();
        });

        // 16. Prediction Evaluations (Ground-Truth Accuracy)
        Schema::create('prediction_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->unique()->constrained('prediction_records')->cascadeOnDelete();
            $table->decimal('actual_crash_multiplier', 8, 2);
            $table->boolean('was_correct');
            $table->decimal('error_margin', 8, 2);
            $table->double('brier_score');
            $table->timestamp('evaluated_at')->useCurrent();
        });

        // 17. ML Datasets
        Schema::create('ml_datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('record_count');
            $table->timestamp('date_from');
            $table->timestamp('date_to');
            $table->string('storage_path', 255);
            $table->char('sha256_hash', 64);
            $table->timestamp('created_at')->useCurrent();
        });

        // 18. ML Models
        Schema::create('ml_models', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 100);
            $table->string('version', 50)->unique();
            $table->string('algorithm', 50);
            $table->string('artifact_path', 255);
            $table->json('metrics');
            $table->enum('status', ['CANDIDATE', 'VALIDATED', 'ACTIVE', 'RETIRED'])->default('CANDIDATE');
            $table->timestamp('promoted_at')->nullable();
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 19. Sync Batches
        Schema::create('sync_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->unique();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $table->unsignedInteger('record_count');
            $table->char('checksum', 64);
            $table->enum('status', ['RECEIVED', 'PROCESSED', 'FAILED'])->default('RECEIVED');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['device_id', 'created_at']);
        });

        // 20. Sync Failures (Dead Letter)
        Schema::create('sync_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->nullable()->constrained('sync_batches')->cascadeOnDelete();
            $table->json('payload');
            $table->text('error_reason');
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // 21. GitHub Repositories
        Schema::create('github_repositories', function (Blueprint $table) {
            $table->id();
            $table->string('repo_name', 100)->unique();
            $table->string('branch_active', 50)->default('main');
            $table->string('webhook_secret', 255);
            $table->char('last_synced_commit', 40)->nullable();
            $table->timestamps();
        });

        // 22. Build Artifacts
        Schema::create('build_artifacts', function (Blueprint $table) {
            $table->id();
            $table->char('commit_hash', 40);
            $table->enum('artifact_type', ['APK_DEBUG', 'APK_RELEASE', 'AAB_RELEASE']);
            $table->string('download_url', 255);
            $table->char('checksum_sha256', 64);
            $table->enum('status', ['SUCCESS', 'FAILED']);
            $table->timestamp('created_at')->useCurrent();
        });

        // 23. VPS Deployments
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->char('commit_hash', 40);
            $table->foreignId('deployed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('release_tag', 50)->nullable();
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'SUCCESS', 'ROLLED_BACK', 'FAILED'])->default('PENDING');
            $table->mediumText('deployment_log')->nullable();
            $table->timestamps();
        });

        // 24. System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('data_type', 20)->default('string');
            $table->timestamps();
        });

        // 25. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('target_entity', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('deployments');
        Schema::dropIfExists('build_artifacts');
        Schema::dropIfExists('github_repositories');
        Schema::dropIfExists('sync_failures');
        Schema::dropIfExists('sync_batches');
        Schema::dropIfExists('ml_models');
        Schema::dropIfExists('ml_datasets');
        Schema::dropIfExists('prediction_evaluations');
        Schema::dropIfExists('prediction_records');
        Schema::dropIfExists('analysis_results');
        Schema::dropIfExists('visual_features');
        Schema::dropIfExists('raw_observations');
        Schema::dropIfExists('game_rounds');
        Schema::dropIfExists('application_releases');
        Schema::dropIfExists('configuration_versions');
        Schema::dropIfExists('device_configurations');
        Schema::dropIfExists('device_heartbeats');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};

