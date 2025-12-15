<?php

namespace Iqonic\FileManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Iqonic\FileManager\Services\FileManagerService
 */
class FileManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'file-manager';
    }
}
