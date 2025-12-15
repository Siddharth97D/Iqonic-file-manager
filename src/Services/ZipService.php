<?php

namespace Iqonic\FileManager\Services;

use Iqonic\FileManager\Models\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ZipService
{
    public function createZip(File $folder): string
    {
        $zipFileName = 'download-' . $folder->id . '-' . time() . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $this->addFolderToZip($folder, $zip);
            $zip->close();
        }

        return $zipPath;
    }

    private function addFolderToZip(File $folder, ZipArchive $zip, string $parentPath = '')
    {
        $files = File::where('parent_id', $folder->id)->get();

        foreach ($files as $file) {
            $relativePath = $parentPath . $file->basename;
            
            if ($file->type === 'folder') {
                $zip->addEmptyDir($relativePath);
                $this->addFolderToZip($file, $zip, $relativePath . '/');
            } else {
                if (Storage::disk($file->disk)->exists($file->path)) {
                    $content = Storage::disk($file->disk)->get($file->path);
                    $zip->addFromString($relativePath, $content);
                }
            }
        }
    }
}
