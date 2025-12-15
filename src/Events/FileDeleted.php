<?php

namespace Iqonic\FileManager\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Models\File;

class FileDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public File $file)
    {
    }
}
