<?php

namespace App\Actions\Project;

use App\Exceptions\ProjectHasOpenTasksException;
use App\Models\Project;
use App\Services\ProjectTaskService;

class DeleteProjectAction
{
    public function __construct(private ProjectTaskService $tasks) {}

    public function handle(Project $project): void
    {
        if ($this->tasks->hasOpenTasks($project)) {
            throw new ProjectHasOpenTasksException;
        }

        $project->delete();
    }
}
