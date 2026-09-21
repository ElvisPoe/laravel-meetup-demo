<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $user->isMemberOf($project);
    }

    public function view(User $user, Task $task): bool
    {
        return $task->project()->where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhereHas('members', fn ($members) => $members->where('user_id', $user->id));
        })->exists();
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isMemberOf($project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->creator_id === $user->id
            || $task->project()->where('owner_id', $user->id)->exists();
    }

    public function complete(User $user, Task $task): bool
    {
        return $task->assignee_id === $user->id
            || $task->project()->where('owner_id', $user->id)->exists();
    }
}
