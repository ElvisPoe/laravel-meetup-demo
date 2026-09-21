<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
            ]),
            'tasks_count' => $this->whenCounted('tasks'),
            'open_tasks_count' => $this->when(isset($this->open_tasks_count), $this->open_tasks_count),
            'members_count' => $this->whenCounted('members'),
            'created_at' => $this->created_at,
        ];
    }
}
