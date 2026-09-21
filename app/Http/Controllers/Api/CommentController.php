<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function index(Task $task): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Comment::class, $task]);

        $comments = $task->comments()
            ->with('user')
            ->oldest('id')
            ->paginate();

        return CommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return CommentResource::make($comment->load('user'))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Task $task, Comment $comment): Response
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
