<?php

use App\Actions\Project\DeleteProjectAction;
use App\Exceptions\ProjectHasOpenTasksException;
use App\Models\Project;
use App\Services\ProjectTaskService;

it('should refuse to delete a project that still has open tasks', function () {
    // Arrange
    $project = new Project;
    $tasks = Mockery::mock(ProjectTaskService::class);
    $tasks->shouldReceive('hasOpenTasks')->once()->with($project)->andReturn(true);

    // Act
    $delete = fn () => (new DeleteProjectAction($tasks))->handle($project);

    // Assert
    expect($delete)->toThrow(ProjectHasOpenTasksException::class, 'A project with open tasks cannot be deleted.');
});
