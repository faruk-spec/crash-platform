<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawObservation extends Model {
    public $timestamps = false;

    protected $fillable = [
        'observation_uuid',
        'device_id',
        'round_id',
        'source_type',
        'raw_payload',
        'captured_at',
        'created_at'
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'captured_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function device(): BelongsTo {
        return $this->belongsTo(Device::class);
    }

    public function round(): BelongsTo {
        return $this->belongsTo(GameRound::class, 'round_id');
    }
}
