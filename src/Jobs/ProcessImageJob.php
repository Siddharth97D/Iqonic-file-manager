<?php

namespace Iqonic\FileManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Iqonic\FileManager\Models\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public File $file)
    {
    }

    public function handle(): void
    {
        // Get settings from database instead of config
        $compressEnabled = \Iqonic\FileManager\Models\Setting::get('compress_images', false);
        $quality = \Iqonic\FileManager\Models\Setting::get('compression_quality', 80);
        $convertToWebP = \Iqonic\FileManager\Models\Setting::get('convert_to_webp', false);

        if (!$compressEnabled && !$convertToWebP) {
            return; // No optimization needed
        }

        try {
             // Get file content
            $disk = Storage::disk($this->file->disk);
            if (!$disk->exists($this->file->path)) {
                Log::error("File not found for processing: {$this->file->id}");
                return;
            }

            $content = $disk->get($this->file->path);
            $image = Image::make($content);

            // WebP Conversion
            if ($convertToWebP) {
                $newPath = pathinfo($this->file->path, PATHINFO_DIRNAME) . '/' . pathinfo($this->file->path, PATHINFO_FILENAME) . '.webp';
                
                $image->encode('webp', $quality);
                $disk->put($newPath, (string) $image);

                // Delete original file
                if ($this->file->path !== $newPath && $disk->exists($this->file->path)) {
                     $disk->delete($this->file->path);
                }

                // Update File Model
                $this->file->update([
                    'path' => $newPath,
                    'basename' => basename($newPath),
                    'extension' => 'webp',
                    'mime_type' => 'image/webp',
                    'size' => $disk->size($newPath),
                ]);

            } elseif ($compressEnabled) {
                // Just compress in place
                $format = $this->file->extension;
                $image->encode($format, $quality);
                $disk->put($this->file->path, (string) $image);
                
                // Update size
                $this->file->update(['size' => $disk->size($this->file->path)]);
            }

            // Generate Thumbnail (always generate)
            $thumb = Image::make($content);
            $thumb->fit(150, 150);
            
            $thumbName = 'thumb_' . basename($this->file->path);
            $thumbPath = dirname($this->file->path) . '/' . $thumbName;
            
            $disk->put($thumbPath, (string) $thumb->encode('webp', 80));

        } catch (\Exception $e) {
            Log::error("Image Processing Failed: " . $e->getMessage());
        }
    }
}
