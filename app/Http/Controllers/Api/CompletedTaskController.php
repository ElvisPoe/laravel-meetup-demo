<?php

namespace App\Http\Controllers\Api;

use App\Actions\Task\CompleteTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class CompletedTaskController extends Controller
{
    public function store(Request $request, Task $task, CompleteTaskAction $complete): TaskResource
    {
        $this->authorize('complete', $task);

        return TaskResource::make($complete->handle($task, $request->user()));
    }
}
