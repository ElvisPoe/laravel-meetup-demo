@props(['user'])

<a href="{{ route('users.show', $user) }}" {{ $attributes->class(['inline-flex items-center gap-2 text-sm text-white hover:text-secondary']) }}>
    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-secondary/20 text-xs font-semibold text-secondary">
        {{ Str::substr($user->name, 0, 1) }}
    </span>
    <span>{{ $user->name }}</span>
</a>
