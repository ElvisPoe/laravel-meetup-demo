<?php

use App\Actions\ProjectMember\AddProjectMemberAction;
use App\Exceptions\DuplicateProjectMemberException;

it('should reject the project owner as a new member', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = projectWithOwner();

    // Act
    $add = fn () => (new AddProjectMemberAction)->handle($project, $owner);

    // Assert
    expect($add)->toThrow(DuplicateProjectMemberException::class, 'This user is already a member of the project.');
});
