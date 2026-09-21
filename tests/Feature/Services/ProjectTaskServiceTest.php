<?php

use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectTaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('should count only open tasks on the project', function () {
    // Arrange
    $project = Project::factory()->create();
    Task::factory()->for($project)->create();
    Task::factory()->for($project)->inProgress()->create();
    Task::factory()->for($project)->done()->create();
    Task::factory()->create();

    // Act
    $count = (new ProjectTaskService)->openTasksCount($project);

    // Assert
    expect($count)->toBe(2);
});

it('should report that a project has open tasks', function () {
    // Arrange
    $project = Project::factory()->create();
    Task::factory()->for($project)->done()->create();
    Task::factory()->for($project)->create();

    // Act
    $hasOpenTasks = (new ProjectTaskService)->hasOpenTasks($project);

    // Assert
    expect($hasOpenTasks)->toBeTrue();
});

it('should report that a project has no open tasks when every task is done', function () {
    // Arrange
    $project = Project::factory()->create();
    Task::factory()->for($project)->done()->count(2)->create();

    // Act
    $hasOpenTasks = (new ProjectTaskService)->hasOpenTasks($project);

    // Assert
    expect($hasOpenTasks)->toBeFalse();
});
