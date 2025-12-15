<?php

namespace Iqonic\FileManager\Services;

use Iqonic\FileManager\Models\File;
use Illuminate\Support\Facades\Auth;

class FileManagerService
{
    /**
     * List files based on filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function listFiles(array $filters = [])
    {
        $query = File::query();
        
        // Filter by Owner (assuming auth)
        if (Auth::check()) {
            $query->where('owner_id', Auth::id());
        }

        // Exclude Trash
        $query->whereNull('deleted_at');

        // Search Query
        if (!empty($filters['search'])) {
            $query->where('basename', 'like', '%' . $filters['search'] . '%');
        }

        // Mime Group Filter (Advanced)
        if (!empty($filters['mime_group']) && $filters['mime_group'] !== 'all') {
            if ($filters['mime_group'] === 'folder') {
                $query->where('type', 'folder');
            } else {
                switch ($filters['mime_group']) {
                    case 'image':
                        $query->where('mime_type', 'like', 'image/%');
                        break;
                    case 'video':
                        $query->where('mime_type', 'like', 'video/%');
                        break;
                    case 'audio':
                        $query->where('mime_type', 'like', 'audio/%');
                        break;
                    case 'document':
                        $query->where(function($q) {
                             $q->where('mime_type', 'like', 'application/pdf')
                               ->orWhere('mime_type', 'like', 'application/msword')
                               ->orWhere('mime_type', 'like', 'application/vnd.openxmlformats-officedocument.%') // Word/Office
                               ->orWhere('mime_type', 'like', 'text/%');
                        });
                        break;
                }
            }
        }

        // Date Range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Scope Logic
        // Determine if we are in "Search/Filter Mode" or "Navigation Mode"
        $isSearching = !empty($filters['search']) || 
                      (!empty($filters['mime_group']) && $filters['mime_group'] !== 'all') ||
                      !empty($filters['date_from']) || 
                      !empty($filters['date_to']);

        if ($isSearching) {
            // Apply Scope
            // If scope is 'current', restrict to current folder
            if (isset($filters['scope']) && $filters['scope'] === 'current' && !empty($filters['folder_id'])) {
                $query->where('parent_id', $filters['folder_id']);
            }
            // If scope is 'global' (default for search), we do NOT restrict parent_id, searching whole drive
        } else {
            // Navigation Mode: Strict parent_id filtering
            if (isset($filters['folder_id']) && $filters['folder_id'] !== null) {
                $query->where('parent_id', $filters['folder_id']);
            } else {
                $query->whereNull('parent_id');
            }
        }

        // Order by latest
        $query->latest();

        $query->with('parent');
        $query->withCount(['subFiles', 'subFolders']);

        return $query->paginate(20);
    }
}
