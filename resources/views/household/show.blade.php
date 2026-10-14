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
        @foreach ($household->appliances as $appliance)
            <h2 class="mt-8 text-xl font-semibold">{{ $appliance->name }} ({{ $appliance->appliance_type }})</h2>

            @if ($appliance->shelves->isEmpty())
                <p class="mt-2">This appliance has no shelves.</p>
            @else
                <table class="mt-2 border">
                    <thead>
                        <tr>
                            <th class="border p-2 text-left">#</th>
                            <th class="border p-2 text-left">Label</th>
                            <th class="border p-2 text-left">Max load (g)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appliance->shelves as $shelf)
                            <tr>
                                <td class="border p-2">{{ $shelf->shelf_order }}</td>
                                <td class="border p-2">{{ $shelf->label }}</td>
                                <td class="border p-2">{{ number_format((float) $shelf->max_load_capacity_g, 0, '.', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endif

    <a href="{{ route('households.index') }}" class="mt-4 inline-block underline">Back to my households</a>
</x-layouts.app>
