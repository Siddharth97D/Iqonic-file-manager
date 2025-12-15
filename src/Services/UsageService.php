<?php

namespace Iqonic\FileManager\Services;

use Iqonic\FileManager\Models\File;
use Illuminate\Support\Facades\DB;

class UsageService
{
    public function getUserUsage(int $userId): int
    {
        return File::where('owner_id', $userId)->sum('size');
    }

    public function getDiskUsage(string $disk): int
    {
        return File::where('disk', $disk)->sum('size');
    }

    public function getUsageByType(int $userId = null): array
    {
        $query = File::query();

        if ($userId) {
            $query->where('owner_id', $userId);
        }

        return $query->select('mime_type', DB::raw('sum(size) as total_size'))
            ->groupBy('mime_type')
            ->pluck('total_size', 'mime_type')
            ->toArray();
    }

    public function checkQuota(int $userId, int $newFileSize): bool
    {
        $currentUsage = $this->getUserUsage($userId);
        $quota = $this->getUserQuota($userId);

        // Convert quota from MB to bytes
        $quotaBytes = $quota * 1024 * 1024;

        return ($currentUsage + $newFileSize) <= $quotaBytes;
    }

    public function getUserQuota(int $userId): int
    {
        // This is a placeholder. In a real app, you might fetch the user's role
        // and get the quota from config based on that role.
        // For now, we return the default quota.
        
        $defaultQuota = config('file-manager.quotas.default_user_quota_mb', 1000);
        
        // Example integration:
        // $user = \App\Models\User::find($userId);
        // if ($user && $user->hasRole('admin')) {
        //     return config('file-manager.quotas.by_role.admin', 10000);
        // }

        return $defaultQuota;
    }
}
