<?php

namespace Database\Seeders;

use App\Actions\ProjectMember\AddProjectMemberAction;
use App\Actions\Task\CompleteTaskAction;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Project Owner',
            'email' => 'owner@example.com',
        ]);

        $member = User::factory()->create([
            'name' => 'Team Member',
            'email' => 'member@example.com',
        ]);

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Website Redesign',
            'description' => 'Launch the new marketing site.',
        ]);

        app(AddProjectMemberAction::class)->handle($project, $member);

        $openTask = Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $owner->id,
            'assignee_id' => $member->id,
            'title' => 'Design homepage mockup',
            'status' => TaskStatus::InProgress,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $owner->id,
            'assignee_id' => $owner->id,
            'title' => 'Write launch copy',
            'status' => TaskStatus::Todo,
        ]);

        $doneTask = Task::factory()->create([
            'project_id' => $project->id,
            'creator_id' => $owner->id,
            'assignee_id' => $member->id,
            'title' => 'Set up staging environment',
            'status' => TaskStatus::InProgress,
        ]);

        app(CompleteTaskAction::class)->handle($doneTask, $member);

        Comment::factory()->create([
            'task_id' => $openTask->id,
            'user_id' => $owner->id,
            'body' => 'Please share the Figma link when ready.',
        ]);
    }
}
