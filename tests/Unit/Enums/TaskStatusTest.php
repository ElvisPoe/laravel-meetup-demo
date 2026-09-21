<?php

use App\Enums\TaskStatus;

it('should label a task status', function (TaskStatus $status, string $label) {
    // Act
    $result = $status->label();

    // Assert
    expect($result)->toBe($label);
})->with([
    'to do' => [TaskStatus::Todo, 'To do'],
    'in progress' => [TaskStatus::InProgress, 'In progress'],
    'done' => [TaskStatus::Done, 'Done'],
]);
