<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileShare extends Model
{
    protected $guarded = [];

    protected $casts = [
        'file_id' => 'integer',
        'expires_at' => 'datetime',
        'max_downloads' => 'integer',
        'downloads_count' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
