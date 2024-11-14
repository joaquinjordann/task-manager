<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'deadline' => $this->deadline ? $this->deadline->format('Y-m-d') : null,
            'completion_percentage' => $this->getCompletionPercentage(),
            'is_overdue' => $this->isOverdue(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'tasks' => $this->when($this->relationLoaded('tasks'), function () {
                return $this->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $task->status,
                        'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                        'created_at' => optional($task->created_at)->format('Y-m-d H:i:s')
                    ];
                });
            }),
            'tasks_count' => $this->when($this->relationLoaded('tasks'), function () {
                return $this->tasks->count();
            }),
            'pending_tasks_count' => $this->when($this->relationLoaded('tasks'), function () {
                return $this->tasks->where('status', 'pendiente')->count();
            })
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toIso8601String()
            ]
        ];
    }
}
