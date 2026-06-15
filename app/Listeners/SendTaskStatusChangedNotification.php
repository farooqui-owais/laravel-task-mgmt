<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTaskStatusChangedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function handle(TaskStatusChanged $event): void
    {
        $assignee = $event->task->assignee;

        if ($assignee === null) {
            return;
        }

        $assignee->notify(new TaskStatusChangedNotification(
            $event->task,
            $event->previousStatus,
            $event->newStatus,
        ));
    }
}
