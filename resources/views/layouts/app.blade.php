@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col bg-[#F4F6F4] text-[#2F4A3A] antialiased">
    <header class="w-full bg-[#2F4A3A] text-white shadow-xs">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-6">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-white">
                {{ config('app.name') }}
            </a>

            @isset($currentUser)
                <nav class="flex items-center gap-6 text-sm text-white/80">
                    <a href="{{ route('consumption.index') }}" class="transition hover:text-white">
                        My consumption
                    </a>
                    <a href="{{ route('profile.show') }}" class="transition hover:text-white">
                        {{ $currentUser->fullName() }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="cursor-pointer underline decoration-white/40 underline-offset-4 transition hover:text-white hover:decoration-white">
                            Log out
                        </button>
                    </form>
                </nav>
            @endisset
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl flex-1 px-6 py-10">
        {{ $slot }}
    </main>

    <footer class="w-full border-t border-[#2F4A3A]/10 py-6">
        <div class="mx-auto max-w-5xl px-6 text-sm text-[#2F4A3A]/55">
            {{ config('app.name') }}
        </div>
    </footer>

    @livewireScripts
</body>
</html>
