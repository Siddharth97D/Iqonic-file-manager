<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileVariant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'size' => 'integer',
        'file_id' => 'integer',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
