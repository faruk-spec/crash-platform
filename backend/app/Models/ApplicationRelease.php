<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationRelease extends Model {
    protected $fillable = [
        'version_name',
        'version_code',
        'release_type',
        'download_url',
        'file_size_bytes',
        'sha256_checksum',
        'signing_key_id',
        'release_notes',
        'is_mandatory',
        'is_published'
    ];

    protected $casts = [
        'version_code' => 'integer',
        'file_size_bytes' => 'integer',
        'is_mandatory' => 'boolean',
        'is_published' => 'boolean'
    ];
}

