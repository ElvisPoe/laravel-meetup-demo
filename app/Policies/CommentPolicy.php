<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user, Task $task): bool
    {
        return $user->can('view', $task);
    }

    public function create(User $user, Task $task): bool
    {
        return $user->can('view', $task);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id
            || $comment->task()->whereHas('project', fn ($project) => $project->where('owner_id', $user->id))->exists();
    }
}
