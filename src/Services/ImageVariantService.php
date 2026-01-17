<?php

namespace Iqonic\FileManager\Services;

use Iqonic\FileManager\Models\File;
use Iqonic\FileManager\Models\FileVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class ImageVariantService
{
    /**
     * Generate all configured image variants for a file
     */
    public function generateVariants(File $file): void
    {
        if (!$this->isEnabled() || !$this->isImage($file)) {
            return;
        }

        $presets = config('file-manager.image_variants.presets', []);
        $disk = Storage::disk($file->disk);

        if (!$disk->exists($file->path)) {
            Log::warning("File not found for variant generation: {$file->path}");
            return;
        }

        try {
            $content = $disk->get($file->path);

            foreach ($presets as $presetName => $preset) {
                $this->generateVariant($file, $presetName, $preset, $content, $disk);
            }

            Log::info("Generated variants for file: {$file->id}");
        } catch (\Exception $e) {
            Log::error("Failed to generate variants for file {$file->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate a single variant
     */
    protected function generateVariant(File $file, string $presetName, array $preset, $content, $disk): void
    {
        try {
            $image = Image::make($content);

            // Apply resize based on fit mode
            $this->applyFit($image, $preset);

            // Encode with quality
            $format = $this->getOutputFormat($file, $preset);
            $quality = $preset['quality'] ?? 85;
            
            $encodedImage = $image->encode($format, $quality);

            // Generate variant path
            $variantPath = $this->generateVariantPath($file->path, $presetName, $format);
            
            // Save variant
            $disk->put($variantPath, (string) $encodedImage);

            // Store variant record
            FileVariant::updateOrCreate(
                [
                    'file_id' => $file->id,
                    'profile' => $presetName,
                ],
                [
                    'disk' => $file->disk,
                    'path' => $variantPath,
                    'size' => $disk->size($variantPath),
                    'mime_type' => "image/{$format}",
                ]
            );

            // Generate WebP variant if enabled
            if (config('file-manager.image_variants.generate_webp', true) && $format !== 'webp') {
                $this->generateWebPVariant($file, $presetName, $preset, $content, $disk);
            }

        } catch (\Exception $e) {
            Log::error("Failed to generate variant '{$presetName}' for file {$file->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate WebP variant
     */
    protected function generateWebPVariant(File $file, string $presetName, array $preset, $content, $disk): void
    {
        try {
            $image = Image::make($content);
            $this->applyFit($image, $preset);
            
            $quality = $preset['quality'] ?? 85;
            $encodedImage = $image->encode('webp', $quality);

            $variantPath = $this->generateVariantPath($file->path, $presetName . '_webp', 'webp');
            $disk->put($variantPath, (string) $encodedImage);

            FileVariant::updateOrCreate(
                [
                    'file_id' => $file->id,
                    'profile' => $presetName . '_webp',
                ],
                [
                    'disk' => $file->disk,
                    'path' => $variantPath,
                    'size' => $disk->size($variantPath),
                    'mime_type' => 'image/webp',
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to generate WebP variant for file {$file->id}: " . $e->getMessage());
        }
    }

    /**
     * Apply fit transformation to image
     */
    protected function applyFit($image, array $preset): void
    {
        $width = $preset['width'];
        $height = $preset['height'];
        $fit = $preset['fit'] ?? 'contain';

        switch ($fit) {
            case 'crop':
                $image->fit($width, $height);
                break;
            case 'cover':
                $image->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
                break;
            case 'contain':
            default:
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                break;
        }
    }

    /**
     * Get output format for variant
     */
    protected function getOutputFormat(File $file, array $preset): string
    {
        // Use original format or override from preset
        if (isset($preset['format'])) {
            return $preset['format'];
        }

        $extension = strtolower($file->extension);
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? $extension : 'jpg';
    }

    /**
     * Generate path for variant
     */
    protected function generateVariantPath(string $originalPath, string $presetName, string $format): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        return "{$directory}/variants/{$filename}_{$presetName}.{$format}";
    }

    /**
     * Get URL for a specific variant
     */
    public function getVariantUrl(File $file, string $presetName): ?string
    {
        $variant = $file->imageVariants()->where('profile', $presetName)->first();

        if (!$variant) {
            // Fallback to original file
            return $file->preview_url;
        }

        // If S3 synced, return S3 URL
        if ($file->s3_sync_status === 'synced' && $variant->disk === 's3') {
            return app(\Iqonic\FileManager\Services\S3SyncService::class)->getPresignedUrl($file);
        }

        // Return local URL
        return route('file-manager.variant.preview', ['file' => $file->id, 'preset' => $presetName]);
    }

    /**
     * Get srcset string for responsive images
     */
    public function getSrcset(File $file): string
    {
        if (!$this->isImage($file)) {
            return '';
        }

        $variants = $file->imageVariants;
        $srcset = [];

        foreach ($variants as $variant) {
            if (!str_ends_with($variant->profile, '_webp')) {
                $url = $this->getVariantUrlFromVariant($variant);
                // Extract width from preset
                $preset = config("file-manager.image_variants.presets.{$variant->profile}");
                if ($preset && isset($preset['width'])) {
                    $srcset[] = "{$url} {$preset['width']}w";
                }
            }
        }

        return implode(', ', $srcset);
    }

    /**
     * Get URL from variant model
     */
    protected function getVariantUrlFromVariant(FileVariant $variant): string
    {
        return route('file-manager.variant.preview', ['file' => $variant->file_id, 'preset' => $variant->profile]);
    }

    /**
     * Delete all variants for a file
     */
    public function deleteVariants(File $file): void
    {
        $variants = $file->imageVariants;
        $disk = Storage::disk($file->disk);

        foreach ($variants as $variant) {
            if ($disk->exists($variant->path)) {
                $disk->delete($variant->path);
            }
            $variant->delete();
        }

        Log::info("Deleted variants for file: {$file->id}");
    }

    /**
     * Check if image variants are enabled
     */
    protected function isEnabled(): bool
    {
        return config('file-manager.image_variants.enabled', true);
    }

    /**
     * Check if file is an image
     */
    protected function isImage(File $file): bool
    {
        return str_starts_with($file->mime_type, 'image/');
    }
}
