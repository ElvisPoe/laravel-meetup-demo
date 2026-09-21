<?php

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;

it('should allow the assignee to complete a task', function () {
    // Arrange
    $assignee = new User;
    $assignee->id = 4;
    $task = new Task;
    $task->assignee_id = 4;

    // Act
    $allowed = (new TaskPolicy)->complete($assignee, $task);

    // Assert
    expect($allowed)->toBeTrue();
});

it('should allow the creator to delete a task', function () {
    // Arrange
    $creator = new User;
    $creator->id = 4;
    $task = new Task;
    $task->creator_id = 4;

    // Act
    $allowed = (new TaskPolicy)->delete($creator, $task);

    // Assert
    expect($allowed)->toBeTrue();
});
