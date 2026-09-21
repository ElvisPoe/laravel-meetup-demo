<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

class ProjectMemberPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $user->isMemberOf($project);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isOwnedBy($user);
    }

    public function delete(User $user, ProjectMember $member): bool
    {
        return $member->project()->where('owner_id', $user->id)->exists();
    }
}
