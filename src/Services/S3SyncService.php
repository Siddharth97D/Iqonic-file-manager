<?php

namespace Iqonic\FileManager\Services;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Iqonic\FileManager\Models\File;
use Iqonic\FileManager\Models\Setting;
use Exception;

class S3SyncService
{
    protected ?S3Client $client = null;

    /**
     * Get S3 Client instance
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $key = Setting::get('s3_key');
        $secret = Setting::get('s3_secret');
        $region = Setting::get('s3_region', 'us-east-1');
        $endpoint = Setting::get('s3_endpoint');

        if (!$key || !$secret) {
            return null;
        }

        // Decrypt values if they are encrypted
        try {
            $key = decrypt($key);
        } catch (Exception $e) { /* Fallback to raw if not encrypted yet */ }

        try {
            $secret = decrypt($secret);
        } catch (Exception $e) { /* Fallback to raw if not encrypted yet */ }

        $config = [
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ];

        if ($endpoint) {
            $config['endpoint'] = $endpoint;
            $config['use_path_style_endpoint'] = true;
        }

        $this->client = new S3Client($config);
        return $this->client;
    }

    /**
     * Test S3 Connection
     */
    public function testConnection($key, $secret, $region, $bucket, $endpoint = null)
    {
        try {
            $config = [
                'version' => 'latest',
                'region' => $region,
                'credentials' => [
                    'key' => $key,
                    'secret' => $secret,
                ],
            ];

            if ($endpoint) {
                $config['endpoint'] = $endpoint;
                $config['use_path_style_endpoint'] = true;
            }

            $client = new S3Client($config);
            $client->headBucket(['Bucket' => $bucket]);
            return ['success' => true, 'message' => 'Connection successful!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Ensure root folder exists in S3
     */
    public function ensureRootFolder($bucket, $rootFolder)
    {
        if (!$rootFolder) return;

        $client = $this->getClient();
        $rootFolder = rtrim($rootFolder, '/') . '/';

        try {
            $client->putObject([
                'Bucket' => $bucket,
                'Key' => $rootFolder,
                'Body' => '',
            ]);
        } catch (Exception $e) {
            Log::error('S3 Root Folder Creation Failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync local file to S3
     */
    public function syncFile(File $file)
    {
        if (!Setting::get('s3_enabled', false)) return;

        $client = $this->getClient();
        $bucket = Setting::get('s3_bucket');
        $rootFolder = Setting::get('s3_root_folder', '');
        
        $localPath = Storage::disk($file->disk)->path($file->path);
        
        if (!file_exists($localPath)) {
            $file->update(['s3_sync_status' => 'failed']);
            return;
        }

        $s3Path = $this->getS3Path($file->path, $rootFolder);

        try {
            $client->putObject([
                'Bucket' => $bucket,
                'Key' => $s3Path,
                'SourceFile' => $localPath,
                'ACL' => 'private',
                'ContentType' => $file->mime_type,
            ]);

            $s3Url = $client->getObjectUrl($bucket, $s3Path);

            $file->update([
                's3_sync_status' => 'synced',
                's3_path' => $s3Path,
                's3_url' => $s3Url
            ]);

            // Sync thumbnail if exists
            if ($file->thumbnail_path && Storage::disk($file->disk)->exists($file->thumbnail_path)) {
                $localThumbPath = Storage::disk($file->disk)->path($file->thumbnail_path);
                $s3ThumbPath = $this->getS3Path($file->thumbnail_path, $rootFolder);
                
                $client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $s3ThumbPath,
                    'SourceFile' => $localThumbPath,
                    'ACL' => 'private',
                    'ContentType' => 'image/jpeg', // Thumbnails are usually jpeg
                ]);

                $s3ThumbUrl = $client->getObjectUrl($bucket, $s3ThumbPath);
                $file->update([
                    's3_thumbnail_path' => $s3ThumbPath,
                    's3_thumbnail_url' => $s3ThumbUrl
                ]);

                // Delete local thumbnail
                Storage::disk($file->disk)->delete($file->thumbnail_path);
            }

            // Delete local file after successful sync
            if (Storage::disk($file->disk)->exists($file->path)) {
                Storage::disk($file->disk)->delete($file->path);
            }
        } catch (Exception $e) {
            Log::error("S3 Upload Failed for File ID {$file->id}: " . $e->getMessage());
            $file->update(['s3_sync_status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Delete file from S3
     */
    public function deleteFromS3(File $file)
    {
        if (!$file->s3_path) return;

        $client = $this->getClient();
        if (!$client) return;
        $bucket = Setting::get('s3_bucket');

        try {
            $client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $file->s3_path,
            ]);
        } catch (Exception $e) {
            Log::error("S3 Deletion Failed for File ID {$file->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate S3 destination path
     */
    protected function getS3Path($localPath, $rootFolder)
    {
        $rootFolder = trim($rootFolder, '/');
        if ($rootFolder) {
            return $rootFolder . '/' . ltrim($localPath, '/');
        }
        return ltrim($localPath, '/');
    }

    /**
     * Move/Rename file in S3 (Recursive for folders)
     */
    public function moveInS3(File $file, $oldS3Path)
    {
        if (!$oldS3Path) return;

        $client = $this->getClient();
        if (!$client) return;
        $bucket = Setting::get('s3_bucket');
        $rootFolder = Setting::get('s3_root_folder', '');
        $newS3Path = $this->getS3Path($file->path, $rootFolder);

        if ($oldS3Path === $newS3Path) return;

        try {
            if ($file->type === 'folder') {
                // S3 doesn't have folders, so we need to move all objects with the old prefix
                $prefix = rtrim($oldS3Path, '/') . '/';
                $results = $client->getPaginator('ListObjects-v2', [
                    'Bucket' => $bucket,
                    'Prefix' => $prefix
                ]);

                foreach ($results as $result) {
                    foreach ($result['Contents'] ?? [] as $object) {
                        $oldKey = $object['Key'];
                        $newKey = str_replace($oldS3Path, $newS3Path, $oldKey);

                        $client->copyObject([
                            'Bucket' => $bucket,
                            'Key' => $newKey,
                            'CopySource' => "{$bucket}/" . rawurlencode($oldKey),
                        ]);

                        $client->deleteObject([
                            'Bucket' => $bucket,
                            'Key' => $oldKey,
                        ]);
                    }
                }
            } else {
                // Single file move
                $client->copyObject([
                    'Bucket' => $bucket,
                    'Key' => $newS3Path,
                    'CopySource' => "{$bucket}/" . rawurlencode($oldS3Path),
                ]);

                $client->deleteObject([
                    'Bucket' => $bucket,
                    'Key' => $oldS3Path,
                ]);

                $s3Url = $client->getObjectUrl($bucket, $newS3Path);

                $file->update([
                    's3_path' => $newS3Path,
                    's3_url' => $s3Url
                ]);

                // Move thumbnail if exists
                if ($file->s3_thumbnail_path) {
                    $newThumbPath = $this->getS3Path($file->thumbnail_path, $rootFolder);
                    $client->copyObject([
                        'Bucket' => $bucket,
                        'Key' => $newThumbPath,
                        'CopySource' => "{$bucket}/" . rawurlencode($file->s3_thumbnail_path),
                    ]);

                    $client->deleteObject([
                        'Bucket' => $bucket,
                        'Key' => $file->s3_thumbnail_path,
                    ]);

                    $file->update([
                        's3_thumbnail_path' => $newThumbPath,
                        's3_thumbnail_url' => $client->getObjectUrl($bucket, $newThumbPath)
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error("S3 Move Failed for File ID {$file->id} ({$file->type}): " . $e->getMessage());
        }
    }
    /**
     * Get Presigned URL for a file or its thumbnail
     */
    public function getPresignedUrl(File $file, $expires = '+1 hour', $useThumbnail = false)
    {
        $path = $useThumbnail ? $file->s3_thumbnail_path : $file->s3_path;
        if (!$path) return null;

        $client = $this->getClient();
        if (!$client) return null;
        
        $bucket = Setting::get('s3_bucket');

        try {
            $cmd = $client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $path,
            ]);

            $request = $client->createPresignedRequest($cmd, $expires);
            return (string) $request->getUri();
        } catch (Exception $e) {
            Log::error("S3 Presigned URL Generation Failed for File ID {$file->id}: " . $e->getMessage());
            return null;
        }
    }
}
