<?php

namespace Iqonic\FileManager\Listeners;

use Iqonic\FileManager\Events\FileUploaded;
use Iqonic\FileManager\Events\FileDeleted;
use Iqonic\FileManager\Events\FileRestored;
use Iqonic\FileManager\Events\FileMoved;
use Iqonic\FileManager\Events\FileShared;
use Iqonic\FileManager\Models\FileActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function handle($event)
    {
        $action = $this->getActionName($event);
        $metadata = $this->getMetadata($event);

        if ($action) {
            FileActivityLog::create([
                'file_id' => $event->file->id,
                'user_id' => Auth::id(),
                'action' => $action,
                'metadata' => $metadata,
            ]);
        }
    }

    protected function getActionName($event): ?string
    {
        return match (get_class($event)) {
            FileUploaded::class => 'uploaded',
            FileDeleted::class => 'deleted',
            FileRestored::class => 'restored',
            FileMoved::class => 'moved',
            FileShared::class => 'shared',
            default => null,
        };
    }

    protected function getMetadata($event): ?array
    {
        if ($event instanceof FileMoved) {
            return ['old_path' => $event->oldPath, 'new_path' => $event->file->path];
        }
        
        if ($event instanceof FileShared) {
            return ['token' => $event->share->token];
        }

        return null;
    }
    
    public function subscribe($events)
    {
        $events->listen(
            [
                FileUploaded::class,
                FileDeleted::class,
                FileRestored::class,
                FileMoved::class,
                FileShared::class,
            ],
            [ActivityLogger::class, 'handle']
        );
    }
}
