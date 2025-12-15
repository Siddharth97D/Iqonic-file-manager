<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

    public function parent()
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function children()
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

    protected $appends = ['human_date'];

    public function getHumanDateAttribute()
    {
        return $this->created_at->diffForHumans(null, true);
    }
}
