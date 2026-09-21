<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $tasks = $project->tasks()
            ->with(['assignee', 'creator'])
            ->withCount('comments')
            ->orderByRaw("case status when 'todo' then 1 when 'in_progress' then 2 else 3 end")
            ->orderBy('id')
            ->paginate();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $task = $project->tasks()->create([
            ...$request->safe()->only(['title', 'description', 'assignee_id', 'status', 'due_at']),
            'creator_id' => $request->user()->id,
        ]);

        return TaskResource::make($task->load(['assignee', 'creator']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project, Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return TaskResource::make(
            $task->load(['assignee', 'creator'])->loadCount('comments')
        );
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): TaskResource
    {
        $task->update($request->safe()->only([
            'title',
            'description',
            'assignee_id',
            'status',
            'due_at',
        ]));

        return TaskResource::make($task->load(['assignee', 'creator']));
    }

    public function destroy(Project $project, Task $task): Response
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
