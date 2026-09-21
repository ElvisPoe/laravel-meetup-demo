<?php

use App\Actions\Task\CompleteTaskAction;
use App\Enums\TaskStatus;
use App\Exceptions\TaskCannotBeCompletedException;
use App\Models\Task;
use App\Models\User;

it('should reject a task without a title', function (string $title) {
    // Arrange
    $task = new Task;
    $task->title = $title;
    $user = new User;

    // Act
    $complete = fn () => (new CompleteTaskAction)->handle($task, $user);

    // Assert
    expect($complete)->toThrow(TaskCannotBeCompletedException::class, 'A task needs a title before it can be completed.');
})->with([
    'empty' => '',
    'blank' => '   ',
]);

it('should reject a task that is already completed', function () {
    // Arrange
    $task = new Task;
    $task->title = 'Ship slides';
    $task->status = TaskStatus::Done;
    $user = new User;

    // Act
    $complete = fn () => (new CompleteTaskAction)->handle($task, $user);

    // Assert
    expect($complete)->toThrow(TaskCannotBeCompletedException::class, 'This task is already completed.');
});
