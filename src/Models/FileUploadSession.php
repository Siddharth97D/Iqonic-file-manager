<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;

class FileUploadSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_chunks' => 'integer',
        'received_chunks' => 'integer',
        'size' => 'integer',
        'user_id' => 'integer',
    ];
}
