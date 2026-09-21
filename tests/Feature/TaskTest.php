<?php

use App\Enums\TaskStatus;
use App\Events\TaskCompleted;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('should list tasks on the owners project', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project, 'task' => $task] = taskOnOwnedProject([
        'title' => 'Ship slides',
    ]);

    // Act
    $response = $this->actingAs($owner)->getJson(route('projects.tasks.index', $project));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.0.id', $task->id)
        ->assertJsonPath('data.0.title', 'Ship slides')
        ->assertJsonPath('data.0.creator.id', $owner->id);
});

it('should show a task the user can access', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project, 'task' => $task] = taskOnOwnedProject([
        'title' => 'Ship slides',
    ]);

    // Act
    $response = $this->actingAs($owner)->getJson(route('projects.tasks.show', [$project, $task]));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.title', 'Ship slides')
        ->assertJsonPath('data.creator.id', $owner->id);
});

it('should show a task page with its comments', function () {
    // Arrange
    ['owner' => $owner, 'task' => $task] = taskOnOwnedProject(
        ['title' => 'Ship slides', 'description' => 'Finish the deck'],
        ['name' => 'Meetup board'],
    );
    Comment::factory()->for($task)->for($owner)->create([
        'body' => 'Looks good',
    ]);

    // Act
    $response = $this->get(route('tasks.show', $task));

    // Assert
    $response->assertSee('Ship slides')
        ->assertSee('Finish the deck')
        ->assertSee('Meetup board')
        ->assertSee('Looks good')
        ->assertSee($owner->name);
});

it('should allow the owner to create a task', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner();

    // Act
    $response = $this->actingAs($owner)->postJson(route('projects.tasks.store', $project), [
        'title' => 'Ship slides',
        'description' => 'Finish the deck',
    ]);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('data.title', 'Ship slides')
        ->assertJsonPath('data.description', 'Finish the deck')
        ->assertJsonPath('data.creator.id', $owner->id);

    $this->assertDatabaseHas('tasks', [
        'project_id' => $project->id,
        'creator_id' => $owner->id,
        'title' => 'Ship slides',
        'description' => 'Finish the deck',
        'status' => TaskStatus::Todo->value,
    ]);
});

it('should allow the owner to complete a task', function () {
    // Arrange
    $this->travelTo('2026-01-15 12:00:00');

    ['owner' => $owner, 'task' => $task] = taskOnOwnedProject([
        'title' => 'Ship slides',
    ]);

    Event::fake([TaskCompleted::class]);

    // Act
    $response = $this->actingAs($owner)->postJson(route('tasks.completions.store', $task));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.status', TaskStatus::Done->value)
        ->assertJsonPath('data.completed_at', '2026-01-15T12:00:00.000000Z');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => TaskStatus::Done->value,
        'completed_at' => '2026-01-15 12:00:00',
    ]);

    Event::assertDispatched(TaskCompleted::class, fn (TaskCompleted $event) => $event->task->is($task));
});

it('should allow the owner to update the task title', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project, 'task' => $task] = taskOnOwnedProject([
        'title' => 'Old title',
    ]);

    // Act
    $response = $this->actingAs($owner)->putJson(route('projects.tasks.update', [$project, $task]), [
        'title' => 'New title',
    ]);

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.title', 'New title');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'New title',
    ]);
});
