<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class File extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'size' => 'integer',
        'owner_id' => 'integer',
        'tenant_id' => 'integer',
        'parent_id' => 'integer',
        'is_favorite' => 'boolean',
        's3_sync_status' => 'string',
        's3_path' => 'string',
        's3_url' => 'string',
    ];

    protected $appends = ['human_date', 'preview_url', 'thumbnail_url'];

    public function getHumanDateAttribute()
    {
        return $this->created_at->diffForHumans(null, true);
    }

    public function getPreviewUrlAttribute()
    {
        // Handle thumbnail request via attribute? 
        // Better: add a dedicated thumbnail_url attribute for ease of use in JS
        if ($this->s3_sync_status === 'synced' && $this->s3_path) {
            return app(\Iqonic\FileManager\Services\S3SyncService::class)->getPresignedUrl($this);
        }

        return route('file-manager.preview', $this->id);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->s3_sync_status === 'synced' && $this->s3_thumbnail_path) {
            return app(\Iqonic\FileManager\Services\S3SyncService::class)->getPresignedUrl($this, '+1 hour', true);
        }

        if ($this->thumbnail_path) {
             return route('file-manager.preview', ['file' => $this->id, 'thumbnail' => 'true']);
        }

        return null;
    }



    public function parent(): BelongsTo
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(File::class, 'parent_id');
    }

    public function subFiles(): HasMany
    {
        return $this->hasMany(File::class, 'parent_id')->where('type', 'file');
    }

    public function subFolders(): HasMany
    {
        return $this->hasMany(File::class, 'parent_id')->where('type', 'folder');
    }

    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }

    public function scopeFolders($query)
    {
        return $query->where('type', 'folder');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(FileVariant::class);
    }

    public function imageVariants(): HasMany
    {
        return $this->hasMany(FileVariant::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(FilePermission::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(FileActivityLog::class);
    }

    public function share(): HasOne
    {
        return $this->hasOne(FileShare::class);
    }

    /**
     * Get URL for a specific image variant
     */
    public function getVariantUrl(string $preset): ?string
    {
        return app(\Iqonic\FileManager\Services\ImageVariantService::class)->getVariantUrl($this, $preset);
    }

    /**
     * Get srcset attribute for responsive images
     */
    public function getSrcsetAttribute(): string
    {
        if (!str_starts_with($this->mime_type, 'image/')) {
            return '';
        }

        return app(\Iqonic\FileManager\Services\ImageVariantService::class)->getSrcset($this);
    }
}
