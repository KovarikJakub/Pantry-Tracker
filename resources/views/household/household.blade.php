<x-layouts::app>
    <h1>My households:</h1>

    @if ($households->isEmpty())
        <p class="mt-4">You are not a member of any household yet.</p>
    @else
        <ul class="mt-4 list-disc pl-6">
            @foreach ($households as $household)
                <li><a href="{{ route('households.show', $household->id) }}">{{ $household->name }}</a></li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('households.store') }}" class="mt-8 flex max-w-sm flex-col gap-3">
        @csrf

        <label for="name">New household name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="border p-2">

        @error('name')
            <p class="text-red-700">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-stone-800 p-2 text-white">Create household</button>
    </form>
</x-layouts::app>
