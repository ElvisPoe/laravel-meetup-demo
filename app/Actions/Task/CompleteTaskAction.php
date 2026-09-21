<?php

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Exceptions\TaskCannotBeCompletedException;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;

class CompleteTaskAction
{
    public function handle(Task $task, User $user): Task
    {
        if (Str::of($task->title)->trim()->isEmpty()) {
            throw new TaskCannotBeCompletedException('A task needs a title before it can be completed.');
        }

        if ($task->status === TaskStatus::Done) {
            throw new TaskCannotBeCompletedException('This task is already completed.');
        }

        $isAssignee = $task->assignee_id === $user->id;
        $isOwner = $task->project()->where('owner_id', $user->id)->exists();

        if (! $isAssignee && ! $isOwner) {
            throw new TaskCannotBeCompletedException('Only the assignee or project owner can complete this task.');
        }

        $task->update([
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);

        TaskCompleted::dispatch($task);

        return $task->refresh()->load(['assignee', 'creator', 'project']);
    }
}
