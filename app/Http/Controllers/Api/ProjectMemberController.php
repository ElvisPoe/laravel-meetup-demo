<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProjectMember\AddProjectMemberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectMemberController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [ProjectMember::class, $project]);

        $members = $project->members()
            ->with('user')
            ->orderBy('id')
            ->get();

        return ProjectMemberResource::collection($members);
    }

    public function store(StoreProjectMemberRequest $request, Project $project, AddProjectMemberAction $add): JsonResponse
    {
        $user = User::query()->findOrFail($request->integer('user_id'));
        $member = $add->handle($project, $user);

        return ProjectMemberResource::make($member)
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Project $project, ProjectMember $member): Response
    {
        $this->authorize('delete', $member);

        $member->delete();

        return response()->noContent();
    }
}
