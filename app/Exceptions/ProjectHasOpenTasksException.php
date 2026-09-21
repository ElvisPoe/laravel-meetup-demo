<?php

namespace App\Exceptions;

class ProjectHasOpenTasksException extends DomainHttpException
{
    public function __construct(string $message = 'A project with open tasks cannot be deleted.')
    {
        parent::__construct($message);
    }
}
