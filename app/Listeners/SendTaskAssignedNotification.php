<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTaskAssignedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(TaskAssigned $event): void
    {
        $assignee = $event->task->assignee;

        if ($assignee === null) {
            return;
        }

        $assignee->notify(new TaskAssignedNotification($event->task));
    }
}
