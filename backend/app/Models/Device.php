<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model {
    protected $fillable = [
        'device_uuid',
        'device_name',
        'hardware_model',
        'os_version',
        'app_version',
        'auth_token',
        'status',
        'last_seen_at'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime'
    ];

    public function configuration(): HasOne {
        return $this->hasOne(DeviceConfiguration::class);
    }

    public function heartbeats(): HasMany {
        return $this->hasMany(DeviceHeartbeat::class);
    }

    public function observations(): HasMany {
        return $this->hasMany(RawObservation::class);
    }

    public function syncBatches(): HasMany {
        return $this->hasMany(SyncBatch::class);
    }
}

