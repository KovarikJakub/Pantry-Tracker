@props(['household'])

<form method="POST" action="{{ route('households.members.store', $household->id) }}" class="mt-8 flex max-w-sm flex-col gap-3">
    @csrf

    <label for="email">Add user by email</label>
    <input id="email" name="email" type="email" required class="border p-2">

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <button type="submit" class="bg-stone-800 p-2 text-white">Add user</button>
</form>
