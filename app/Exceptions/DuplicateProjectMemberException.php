<?php

namespace App\Exceptions;

class DuplicateProjectMemberException extends DomainHttpException
{
    public function __construct(string $message = 'This user is already a member of the project.')
    {
        parent::__construct($message);
    }
}
