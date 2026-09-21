<?php

it('should treat the project owner as a member', function () {
    // Arrange
    ['owner' => $owner, 'project' => $project] = projectWithOwner();

    // Act
    $member = $owner->isMemberOf($project);

    // Assert
    expect($member)->toBeTrue();
});
