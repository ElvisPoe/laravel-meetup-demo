<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-primary font-sans text-white antialiased">
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,_#ff276828,_transparent_55%)]"></div>

        <div class="relative flex min-h-screen flex-col">
            <header class="border-b border-white/10 bg-primary/80 backdrop-blur">
                <div class="mx-auto flex h-16 w-full max-w-5xl items-center justify-between px-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="size-2.5 rounded-full bg-secondary"></span>
                        <span class="text-sm font-semibold tracking-wide">Task board</span>
                    </a>
                    <span class="text-xs font-medium tracking-widest text-white/40 uppercase">Projects</span>
                </div>
            </header>

            <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-8 px-6 py-10">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
