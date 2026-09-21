<?php

use App\Models\Comment;
use App\Models\User;
use App\Policies\CommentPolicy;

it('should allow the author to delete a comment', function () {
    // Arrange
    $author = new User;
    $author->id = 4;
    $comment = new Comment;
    $comment->user_id = 4;

    // Act
    $allowed = (new CommentPolicy)->delete($author, $comment);

    // Assert
    expect($allowed)->toBeTrue();
});
