<?php

namespace Iqonic\FileManager\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Models\File;

class FileCompressed
{
    use Dispatchable, SerializesModels;

    public function __construct(public File $file)
    {
    }
}
