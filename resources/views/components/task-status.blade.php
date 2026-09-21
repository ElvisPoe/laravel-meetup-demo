@use('App\Enums\TaskStatus')

@props(['status'])

<span {{ $attributes->class([
    'inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold tracking-wide',
    'bg-white/10 text-white/70' => $status === TaskStatus::Todo,
    'bg-secondary text-white' => $status === TaskStatus::InProgress,
    'bg-secondary/15 text-secondary' => $status === TaskStatus::Done,
]) }}>
    {{ $status->label() }}
</span>
