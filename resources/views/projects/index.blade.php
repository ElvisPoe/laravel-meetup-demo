<x-layouts.app :title="'Task board'">
    <header class="flex flex-col gap-3">
        <p class="text-xs font-semibold tracking-[0.2em] text-secondary uppercase">Overview</p>
        <h1 class="text-4xl font-semibold tracking-tight">Your projects</h1>
        <p class="text-white/60">Owners, members, and assignees for every task in the workspace.</p>
    </header>

        @forelse ($projects as $project)
            <x-panel class="border-l-4 border-l-secondary">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex flex-col gap-2">
                            <h2 class="text-2xl font-semibold tracking-tight">{{ $project->name }}</h2>
                            @if ($project->description)
                                <p class="text-white/60">{{ $project->description }}</p>
                            @endif
                        </div>
                        <p class="rounded-full bg-secondary/15 px-3 py-1 text-sm font-medium text-secondary">
                            {{ $project->tasks->count() }} {{ Str::plural('task', $project->tasks->count()) }}
                        </p>
                    </div>

                    <dl class="flex flex-col gap-4 text-sm sm:flex-row sm:flex-wrap sm:gap-x-10">
                        <div class="flex flex-col gap-2">
                            <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Owner</dt>
                            <dd>
                                <x-person-link :user="$project->owner" />
                            </dd>
                        </div>
                        <div class="flex flex-col gap-2">
                            <dt class="text-xs font-semibold tracking-widest text-white/40 uppercase">Members</dt>
                            <dd class="flex flex-wrap gap-3">
                                @forelse ($project->members as $member)
                                    <x-person-link :user="$member->user" />
                                @empty
                                    <span class="text-white/40">No members yet</span>
                                @endforelse
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="flex flex-col gap-3">
                    <h3 class="text-xs font-semibold tracking-widest text-white/40 uppercase">Tasks</h3>

                    @forelse ($project->tasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="flex flex-col gap-3 rounded-2xl border border-white/10 bg-primary/40 px-4 py-4 transition hover:border-secondary/50 hover:bg-secondary/10 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-1">
                                <p class="font-medium">{{ $task->title }}</p>
                                <p class="text-sm text-white/50">
                                    Assignee:
                                    {{ $task->assignee?->name ?? 'Unassigned' }}
                                </p>
                            </div>
                            <span class="max-sm:hidden">
                                <x-task-status :status="$task->status" />
                            </span>
                        </a>
                    @empty
                        <p class="text-sm text-white/40">No tasks yet.</p>
                    @endforelse
                </div>
            </x-panel>
        @empty
            <x-panel class="border-dashed text-center text-white/50">
                No projects yet.
            </x-panel>
        @endforelse
</x-layouts.app>
