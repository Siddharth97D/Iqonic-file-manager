<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilePermission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'file_id' => 'integer',
        'user_id' => 'integer',
        'can_read' => 'boolean',
        'can_write' => 'boolean',
        'can_share' => 'boolean',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
