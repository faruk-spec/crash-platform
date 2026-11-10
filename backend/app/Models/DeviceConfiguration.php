<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceConfiguration extends Model {
    protected $fillable = [
        'device_id',
        'config_version',
        'min_multiplier',
        'max_multiplier',
        'activation_mode',
        'custom_min',
        'custom_max',
        'min_confidence',
        'collection_enabled',
        'predictor_enabled',
        'sync_interval_seconds',
        'active_model_version'
    ];

    protected $casts = [
        'min_multiplier' => 'float',
        'max_multiplier' => 'float',
        'custom_min' => 'float',
        'custom_max' => 'float',
        'min_confidence' => 'float',
        'collection_enabled' => 'boolean',
        'predictor_enabled' => 'boolean',
        'config_version' => 'integer',
        'sync_interval_seconds' => 'integer'
    ];

    public function device(): BelongsTo {
        return $this->belongsTo(Device::class);
    }
}

