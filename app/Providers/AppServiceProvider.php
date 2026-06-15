<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Listeners\SendTaskAssignedNotification;
use App\Listeners\SendTaskStatusChangedNotification;
use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        //$this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        //$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class); 
    }

    public function boot(): void
    {
        // Policies
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Events → Listeners
        Event::listen(TaskAssigned::class, SendTaskAssignedNotification::class);
        Event::listen(TaskStatusChanged::class, SendTaskStatusChangedNotification::class);

        // Default password rules
        Password::defaults(function (): Password {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            return config('app.frontend_url') . "/password-reset/{$token}?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
