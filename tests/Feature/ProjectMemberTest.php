<?php

use App\Mail\MemberAddedToProjectMail;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('should list members of a project for the owner', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner();
    $member = User::factory()->create(['name' => 'Ada Lovelace']);
    ProjectMember::factory()->for($project)->for($member)->create();

    // Act
    $response = $this->actingAs($owner)->getJson(route('projects.members.index', $project));

    // Assert
    $response->assertOk()
        ->assertJsonPath('data.0.user.id', $member->id)
        ->assertJsonPath('data.0.user.name', 'Ada Lovelace');
});

it('should allow the owner to add a member to a project', function () {
    // Arrange
    Mail::fake();

    ['owner' => $owner, 'project' => $project] = actingAsProjectOwner();
    $member = User::factory()->create();

    // Act
    $response = $this->actingAs($owner)->postJson(route('projects.members.store', $project), [
        'user_id' => $member->id,
    ]);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('data.user.id', $member->id)
        ->assertJsonPath('data.user.email', $member->email);

    $this->assertDatabaseHas('project_members', [
        'project_id' => $project->id,
        'user_id' => $member->id,
    ]);

    Mail::assertQueued(MemberAddedToProjectMail::class, fn (MemberAddedToProjectMail $mail) => $mail->hasTo($member->email));
});
