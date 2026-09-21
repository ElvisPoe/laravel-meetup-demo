<?php

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('should show the projects and assigned tasks for a user', function () {
    // Arrange
    $user = User::factory()->create([
        'name' => 'Ada Lovelace',
    ]);
    Project::factory()->for($user, 'owner')->create([
        'name' => 'Meetup board',
    ]);
    $joined = Project::factory()->create([
        'name' => 'Design system',
    ]);
    ProjectMember::factory()->for($joined)->for($user)->create();
    Task::factory()->for($joined)->for($user, 'assignee')->create([
        'title' => 'Ship slides',
    ]);

    // Act
    $response = $this->get(route('users.show', $user));

    // Assert
    $response->assertSee('Ada Lovelace')
        ->assertSee($user->email)
        ->assertSee('Meetup board')
        ->assertSee('Design system')
        ->assertSee('Ship slides');
});
