<?php

declare(strict_types=1);

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'field_changed' => $this->field_changed,
            'old_value'     => $this->old_value,
            'new_value'     => $this->new_value,
            'changed_by'    => $this->whenLoaded('changedBy', fn () => [
                'id'   => $this->changedBy->id,
                'name' => $this->changedBy->name,
            ]),
            'changed_at' => $this->created_at->toIso8601String(),
        ];
    }
}
