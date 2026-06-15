<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("New Task Assigned: {$this->task->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned a new task.")
            ->line("**Task:** {$this->task->title}")
            ->line("**Priority:** {$this->task->priority->label()}")
            ->line("**Due Date:** {$this->task->due_date?->format('Y-m-d H:i')}")
            ->line($this->task->description ?? '')
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Thank you for using our task management system.');
    }
}
