<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in-progress';
    case Completed  = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
        };
    }
}
