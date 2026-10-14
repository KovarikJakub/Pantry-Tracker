<x-layouts.app :title="$household->name">
    <h1 class="text-2xl font-semibold">{{ $household->name }}</h1>

    @if ($isManager)
        <form method="POST" action="{{ route('households.members.store', $household->id) }}" class="mt-8 flex max-w-sm flex-col gap-3">
            @csrf

            <label for="email">Add user by email</label>
            <input id="email" name="email" type="email" required class="border p-2">

            @if (session('status'))
                <p>{{ session('status') }}</p>
            @endif

            <button type="submit" class="bg-stone-800 p-2 text-white">Add user</button>
        </form>
        <form method="POST" action="{{ route('households.appliances.store', $household->id) }}" class="mt-8 flex max-w-sm flex-col gap-3">
            @csrf

            <label for="name">Appliance Name</label>
            <input id="name" name="name" type="text" required class="border p-2">

            <label for="appliance_type">Appliance Type</label>
            <select id="appliance_type" name="appliance_type" required class="border p-2">
                <option value="fridge">Fridge</option>
                <option value="freezer">Freezer</option>
                <option value="shelf">Shelf</option>
                <option value="pantry">Pantry</option>
                <option value="cabinet">Cabinet</option>
            </select>

            <button type="submit" class="bg-stone-800 p-2 text-white">Add Appliance</button>
        </form>
    @endif
    @if ($household->appliances->isEmpty())
        <p class="mt-4">There are no appliances in this household.</p>
    @else
        <ul class="mt-4 list-disc pl-6">
            @foreach ($household->appliances as $appliance)
                <li>{{ $appliance->name }}</li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('households.index') }}" class="mt-4 inline-block underline">Back to my households</a>
</x-layouts.app>
