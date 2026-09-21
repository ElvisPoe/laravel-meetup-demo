<?php

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('should list comments on a task for the owner', function () {
    // Arrange
    ['owner' => $owner, 'task' => $task] = taskOnOwnedProject();
    $comment = Comment::factory()->for($task)->for($owner)->create([
        'body' => 'Looks good',
    ]);

    // Act
    $response = $this->actingAs($owner)->getJson(route('tasks.comments.index', $task));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.0.id', $comment->id)
        ->assertJsonPath('data.0.body', 'Looks good')
        ->assertJsonPath('data.0.author.id', $owner->id);
});

it('should allow the owner to comment on a task', function () {
    // Arrange
    ['owner' => $owner, 'task' => $task] = taskOnOwnedProject();

    // Act
    $response = $this->actingAs($owner)->postJson(route('tasks.comments.store', $task), [
        'body' => 'Looks good',
    ]);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('data.body', 'Looks good')
        ->assertJsonPath('data.author.id', $owner->id);

    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $owner->id,
        'body' => 'Looks good',
    ]);
});
