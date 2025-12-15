<?php

namespace Iqonic\FileManager\Policies;

use Iqonic\FileManager\Models\File;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Log;

class FilePolicy
{
    use HandlesAuthorization;

    public function view(User $user, File $file): bool
    {
        if ($user->id == $file->owner_id) {
            return true;
        }

        if ($file->visibility === 'public') {
            return true;
        }

        // Check file permissions
        $permission = $file->permissions()->where('user_id', $user->id)->first();
        if ($permission && $permission->can_read) {
            return true;
        }

        return false;
    }

    public function update(User $user, File $file): bool
    {
        if ($user->id == $file->owner_id) {
            return true;
        }

        $permission = $file->permissions()->where('user_id', $user->id)->first();
        if ($permission && $permission->can_write) {
            return true;
        }

        return false;
    }

    public function delete(User $user, File $file): bool
    {
        // Only owner can delete for now, or maybe admin
        return $user->id == $file->owner_id;
    }

    public function share(User $user, File $file): bool
    {
        if ($user->id == $file->owner_id) {
            return true;
        }

        $permission = $file->permissions()->where('user_id', $user->id)->first();
        if ($permission && $permission->can_share) {
            return true;
        }

        return false;
    }
}
