<?php

namespace App\Actions\ProjectMember;

use App\Events\MemberAddedToProject;
use App\Exceptions\DuplicateProjectMemberException;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

class AddProjectMemberAction
{
    public function handle(Project $project, User $user): ProjectMember
    {
        if ($project->isOwnedBy($user) || $user->isMemberOf($project)) {
            throw new DuplicateProjectMemberException;
        }

        try {
            $member = $project->members()->create([
                'user_id' => $user->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new DuplicateProjectMemberException;
        }

        MemberAddedToProject::dispatch($member);

        return $member->load(['user', 'project']);
    }
}
