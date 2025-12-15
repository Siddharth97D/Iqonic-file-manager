<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'file_manager_settings';
    
    protected $fillable = ['key', 'value'];
    
    protected $casts = [
        'value' => 'array',
    ];
    
    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
