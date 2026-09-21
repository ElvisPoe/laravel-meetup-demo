<?php

use App\Models\User;

it('should recognize the project owner', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = projectWithOwner();

    // Act
    $owned = $project->isOwnedBy($owner);

    // Assert
    expect($owned)->toBeTrue();
});

it('should reject a user who does not own the project', function () {
    // Arrange
    ['project' => $project] = projectWithOwner();
    $someoneElse = new User;
    $someoneElse->id = 2;

    // Act
    $owned = $project->isOwnedBy($someoneElse);

    // Assert
    expect($owned)->toBeFalse();
});
