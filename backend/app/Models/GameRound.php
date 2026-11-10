<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model {
    public $timestamps = false;

    protected $fillable = [
        'round_uuid',
        'server_round_id',
        'crash_multiplier',
        'started_at',
        'crashed_at',
        'duration_ms',
        'provably_fair_hash',
        'animation_metrics',
        'status',
        'created_at'
    ];

    protected $casts = [
        'crash_multiplier' => 'float',
        'started_at' => 'datetime',
        'crashed_at' => 'datetime',
        'duration_ms' => 'integer',
        'animation_metrics' => 'array',
        'created_at' => 'datetime'
    ];

    public function observations(): HasMany {
        return $this->hasMany(RawObservation::class, 'round_id');
    }

    public function visualFeatures(): HasMany {
        return $this->hasMany(VisualFeature::class, 'round_id');
    }

    public function predictions(): HasMany {
        return $this->hasMany(PredictionRecord::class, 'round_id');
    }
}
