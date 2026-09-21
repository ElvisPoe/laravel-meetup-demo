<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function show(Task $task): View
    {
        $task->load([
            'project.owner',
            'assignee',
            'creator',
            'comments' => fn ($query) => $query->with('user')->oldest('id'),
        ]);

        return view('tasks.show', [
            'task' => $task,
        ]);
    }
}
