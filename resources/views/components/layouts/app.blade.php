@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-5xl flex-col px-6">
        <header class="flex h-16 items-center justify-between border-b border-stone-200">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</a>
        </header>

        <main class="flex-1 py-12">
            {{ $slot }}
        </main>

        <footer class="border-t border-stone-200 py-6 text-sm text-stone-500">
            {{ config('app.name') }}
        </footer>
    </div>
</body>
</html>
