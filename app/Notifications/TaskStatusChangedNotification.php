<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly string $previousStatus,
        private readonly string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Task Status Updated: {$this->task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A task assigned to you has been updated.")
            ->line("**Task:** {$this->task->title}")
            ->line("**Previous Status:** {$this->previousStatus}")
            ->line("**New Status:** {$this->newStatus}")
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Thank you for using our task management system.');
    }
}
