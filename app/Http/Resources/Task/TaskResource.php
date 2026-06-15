<?php

declare(strict_types=1);

namespace App\Http\Resources\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'priority'    => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'due_date'    => $this->due_date?->toIso8601String(),
            'assigned_to' => $this->whenLoaded('assignee', fn () => [
                'id'    => $this->assignee->id,
                'name'  => $this->assignee->name,
                'email' => $this->assignee->email,
            ]),
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'histories'  => TaskHistoryResource::collection($this->whenLoaded('histories')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
