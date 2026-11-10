<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncBatch extends Model {
    public $timestamps = false;

    protected $fillable = [
        'batch_uuid',
        'device_id',
        'record_count',
        'checksum',
        'status',
        'created_at'
    ];

    protected $casts = [
        'record_count' => 'integer',
        'created_at' => 'datetime'
    ];

    public function device(): BelongsTo {
        return $this->belongsTo(Device::class);
    }

    public function failures(): HasMany {
        return $this->hasMany(SyncFailure::class, 'batch_id');
    }
}

