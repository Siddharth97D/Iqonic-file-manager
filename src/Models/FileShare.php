<?php

namespace Iqonic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FileShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'token',
        'password_hash',
        'expires_at',
        'downloads',
        'max_downloads'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'downloads' => 'integer',
        'max_downloads' => 'integer',
    ];

    /**
     * Get the file that is shared.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who created the share.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Check if the share link is valid (not expired and download limit not reached).
     */
    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_downloads !== null && $this->downloads >= $this->max_downloads) {
            return false;
        }

        return true;
    }

    /**
     * Validate the password.
     */
    public function checkPassword(?string $password): bool
    {
        if (!$this->password_hash) {
            return true; 
        }

        if (empty($password)) {
            return false;
        }

        return Hash::check($password, $this->password_hash);
    }
}
