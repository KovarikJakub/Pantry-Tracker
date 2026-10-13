<x-layouts.app :title="$household->name">
    <h1 class="text-2xl font-semibold">{{ $household->name }}</h1>

    <a href="{{ route('households.index') }}" class="mt-4 inline-block underline">Back to my households</a>
</x-layouts.app>
