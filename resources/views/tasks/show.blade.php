<x-layouts.app :title="$task->title">
    <a href="{{ route('home') }}" class="text-sm text-white/50 transition hover:text-secondary">Back to board</a>

        <x-panel>
            <header class="flex flex-col gap-4">
                <p class="text-xs font-semibold tracking-[0.2em] text-secondary uppercase">{{ $task->project->name }}</p>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <h1 class="text-3xl font-semibold tracking-tight">{{ $task->title }}</h1>
                    <x-task-status :status="$task->status" />
                </div>
                @if ($task->description)
                    <p class="text-white/60">{{ $task->description }}</p>
                @endif
            </header>

            <dl class="grid gap-5 text-sm sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Assignee</dt>
                    <dd>
                        @if ($task->assignee)
                            <x-person-link :user="$task->assignee" />
                        @else
                            <span class="text-white/40">Unassigned</span>
                        @endif
                    </dd>
                </div>
                <div class="flex flex-col gap-2">
                    <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Created by</dt>
                    <dd>
                        <x-person-link :user="$task->creator" />
                    </dd>
                </div>
                <div class="flex flex-col gap-2">
                    <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Owner</dt>
                    <dd>
                        <x-person-link :user="$task->project->owner" />
                    </dd>
                </div>
                <div class="flex flex-col gap-2">
                    <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Due</dt>
                    <dd class="text-white/80">{{ $task->due_at?->format('M j, Y') ?? 'No due date' }}</dd>
                </div>
                @if ($task->completed_at)
                    <div class="flex flex-col gap-2">
                        <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Completed</dt>
                        <dd class="text-white/80">{{ $task->completed_at->format('M j, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </x-panel>

        <section class="flex flex-col gap-4">
            <h2 class="text-xs font-semibold tracking-widest text-white/40 uppercase">Comments</h2>

            @forelse ($task->comments as $comment)
                <article class="flex flex-col gap-3 rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <x-person-link :user="$comment->user" />
                        <time class="text-xs text-white/40" datetime="{{ $comment->created_at->toIso8601String() }}">
                            {{ $comment->created_at->format('M j, Y') }}
                        </time>
                    </div>
                    <p class="text-white/80">{{ $comment->body }}</p>
                </article>
            @empty
                <p class="text-sm text-white/40">No comments yet.</p>
            @endforelse
        </section>
</x-layouts.app>
