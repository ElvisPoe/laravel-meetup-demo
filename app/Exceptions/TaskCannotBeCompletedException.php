<?php

namespace App\Exceptions;

class TaskCannotBeCompletedException extends DomainHttpException
{
    public function __construct(string $message = 'This task cannot be completed.')
    {
        parent::__construct($message);
    }
}
