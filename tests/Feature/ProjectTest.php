<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('should list projects for the authenticated user', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner([
        'name' => 'Meetup board',
    ]);

    // Act
    $response = $this->actingAs($owner)->getJson(route('projects.index'));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.0.id', $project->id)
        ->assertJsonPath('data.0.name', 'Meetup board')
        ->assertJsonPath('data.0.owner.id', $owner->id);
});

it('should show a project the user can access', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner([
        'name' => 'Meetup board',
    ]);

    // Act
    $response = $this->actingAs($owner)->getJson(route('projects.show', $project));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.id', $project->id)
        ->assertJsonPath('data.name', 'Meetup board')
        ->assertJsonPath('data.owner.id', $owner->id);
});

it('should show projects and tasks on the board', function () {
    // Arrange
    ['owner' => $owner] = taskOnOwnedProject(
        ['title' => 'Ship slides'],
        ['name' => 'Meetup board'],
    );

    // Act
    $response = $this->get(route('home'));

    // Assert
    $response->assertSee('Meetup board')
        ->assertSee('Ship slides')
        ->assertSee($owner->name);
});

it('should allow the owner to create a project', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson(route('projects.store'), [
        'name' => 'Meetup board',
        'description' => 'Slides and notes',
    ]);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('data.name', 'Meetup board')
        ->assertJsonPath('data.description', 'Slides and notes')
        ->assertJsonPath('data.owner.id', $user->id);

    $this->assertDatabaseHas('projects', [
        'owner_id' => $user->id,
        'name' => 'Meetup board',
        'description' => 'Slides and notes',
    ]);
});

it('should allow the owner to update the project name', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner([
        'name' => 'Old name',
    ]);

    // Act
    $response = $this->actingAs($owner)->putJson(route('projects.update', $project), [
        'name' => 'New name',
    ]);

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.name', 'New name');

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'New name',
    ]);
});
