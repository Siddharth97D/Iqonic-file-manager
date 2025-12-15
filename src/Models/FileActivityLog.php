<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'file_id' => 'integer',
        'user_id' => 'integer',
        'metadata' => 'array',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
