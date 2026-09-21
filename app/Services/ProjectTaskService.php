<?php

namespace App\Services;

use App\Models\Project;

class ProjectTaskService
{
    public function openTasksCount(Project $project): int
    {
        return $project->tasks()->open()->count();
    }

    public function hasOpenTasks(Project $project): bool
    {
        return $this->openTasksCount($project) > 0;
    }
}
