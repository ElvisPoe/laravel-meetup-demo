<?php

namespace App\Http\Controllers\Api;

use App\Actions\Project\DeleteProjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->with('owner')
            ->withCount([
                'tasks',
                'tasks as open_tasks_count' => fn ($query) => $query->open(),
                'members',
            ])
            ->where(function ($query) use ($request) {
                $query->whereBelongsTo($request->user(), 'owner')
                    ->orWhereHas('members', fn ($members) => $members->whereBelongsTo($request->user()));
            })
            ->latest('id')
            ->paginate();

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::query()->create([
            ...$request->safe()->only(['name', 'description']),
            'owner_id' => $request->user()->id,
        ]);

        return ProjectResource::make($project->load('owner'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        $project->load('owner')->loadCount([
            'tasks',
            'tasks as open_tasks_count' => fn ($query) => $query->open(),
            'members',
        ]);

        return ProjectResource::make($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $project->update($request->safe()->only(['name', 'description']));

        return ProjectResource::make($project->load('owner'));
    }

    public function destroy(Project $project, DeleteProjectAction $delete): Response
    {
        $this->authorize('delete', $project);

        $delete->handle($project);

        return response()->noContent();
    }
}
