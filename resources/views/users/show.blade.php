<x-layouts.app :title="$user->name">
    <a href="{{ route('home') }}" class="text-sm text-white/50 transition hover:text-secondary">Back to board</a>

        <x-panel>
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <span class="flex size-16 shrink-0 items-center justify-center rounded-full bg-secondary text-2xl font-semibold text-white">
                    {{ Str::substr($user->name, 0, 1) }}
                </span>
                <div class="flex flex-col gap-1">
                    @if ($user->ownedProjects->isNotEmpty() || $user->projectMemberships->isNotEmpty())
                        <p class="text-xs font-semibold tracking-[0.2em] text-secondary uppercase">
                            @if ($user->ownedProjects->isNotEmpty())
                                Owner
                            @endif
                            @if ($user->ownedProjects->isNotEmpty() && $user->projectMemberships->isNotEmpty())
                                ·
                            @endif
                            @if ($user->projectMemberships->isNotEmpty())
                                Member
                            @endif
                        </p>
                    @endif
                    <h1 class="text-3xl font-semibold tracking-tight">{{ $user->name }}</h1>
                    <p class="text-white/60">{{ $user->email }}</p>
                </div>
            </header>
        </x-panel>

        <section class="flex flex-col gap-3">
            <h2 class="text-xs font-semibold tracking-widest text-white/40 uppercase">Owns</h2>
            @forelse ($user->ownedProjects as $project)
                <p class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ $project->name }}</p>
            @empty
                <p class="text-sm text-white/40">Does not own any projects.</p>
            @endforelse
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xs font-semibold tracking-widest text-white/40 uppercase">Member of</h2>
            @forelse ($user->projectMemberships as $membership)
                <p class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">{{ $membership->project->name }}</p>
            @empty
                <p class="text-sm text-white/40">Not a member of any projects.</p>
            @endforelse
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-xs font-semibold tracking-widest text-white/40 uppercase">Assigned tasks</h2>
            @forelse ($user->assignedTasks as $task)
                <a href="{{ route('tasks.show', $task) }}" class="flex flex-col gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-4 transition hover:border-secondary/50 hover:bg-secondary/10 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col gap-1">
                        <p class="font-medium">{{ $task->title }}</p>
                        <p class="text-sm text-white/50">{{ $task->project->name }}</p>
                    </div>
                    <x-task-status :status="$task->status" />
                </a>
            @empty
                <p class="text-sm text-white/40">No assigned tasks.</p>
            @endforelse
        </section>
</x-layouts.app>
