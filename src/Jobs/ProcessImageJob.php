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
        // Get settings from database
        $compressEnabled = \Iqonic\FileManager\Models\Setting::get('compress_images', false);
        $quality = \Iqonic\FileManager\Models\Setting::get('compression_quality', 80);
        $convertToWebP = \Iqonic\FileManager\Models\Setting::get('convert_to_webp', false);
        
        // Always proceed to generate thumbnails, but optimization only happens if enabled
        $needsOptimization = $compressEnabled || $convertToWebP;

        try {
             // Get file content
            $disk = Storage::disk($this->file->disk);
            if (!$disk->exists($this->file->path)) {
                Log::error("File not found for processing: {$this->file->id}");
                return;
            }

            $content = $disk->get($this->file->path);
            $oldPath = $this->file->path;
            $image = Image::make($content);

            if ($needsOptimization) {
                // WebP Conversion
                if ($convertToWebP) {
                    $newPath = pathinfo($oldPath, PATHINFO_DIRNAME) . '/' . pathinfo($oldPath, PATHINFO_FILENAME) . '.webp';
                    
                    Log::info("Converting image to WebP: {$oldPath} -> {$newPath}");
                    
                    $image->encode('webp', $quality);
                    $disk->put($newPath, (string) $image);

                    // Update File Model
                    $this->file->update([
                        'path' => $newPath,
                        'basename' => basename($newPath),
                        'extension' => 'webp',
                        'mime_type' => 'image/webp',
                        'size' => $disk->size($newPath),
                    ]);

                    // Delete original file
                    if ($oldPath !== $newPath && $disk->exists($oldPath)) {
                         $disk->delete($oldPath);
                    }

                } elseif ($compressEnabled) {
                    // Just compress in place
                    $format = $this->file->extension;
                    $q = $quality;
                    
                    // Normalize quality for PNG (0-9)
                    if (strtolower($format) === 'png') {
                        $q = (int) ($quality / 10);
                        if ($q > 9) $q = 9;
                    }

                    Log::info("Compressing image in place: {$oldPath} (Quality: {$q})");
                    
                    $image->encode($format, $q);
                    $disk->put($oldPath, (string) $image);
                    
                    // Update size
                    $this->file->update(['size' => $disk->size($oldPath)]);
                }
            }

            // Generate Thumbnail
            Log::info("Generating thumbnail for: " . $this->file->path);
            $thumb = Image::make($content);
            $thumb->fit(150, 150);
            
            // Always use webp for thumbnails for best performance
            $thumbName = 'thumb_' . pathinfo($this->file->path, PATHINFO_FILENAME) . '.webp';
            $thumbPath = dirname($this->file->path) . '/' . $thumbName;
            
            $disk->put($thumbPath, (string) $thumb->encode('webp', 80));
            
            $this->file->update(['thumbnail_path' => $thumbPath]);
            Log::info("Thumbnail generated: {$thumbPath}");

            // Generate Image Variants
            if (config('file-manager.image_variants.enabled', true)) {
                Log::info("Generating image variants for: {$this->file->id}");
                app(\Iqonic\FileManager\Services\ImageVariantService::class)->generateVariants($this->file);
            }

        } catch (\Exception $e) {
            Log::error("Image Processing Failed: " . $e->getMessage());
        }
    }
}
