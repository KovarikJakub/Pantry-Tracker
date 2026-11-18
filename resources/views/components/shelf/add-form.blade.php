@props(['household', 'appliance'])

@php($shelfErrors = $errors->getBag('shelf_'.$appliance->id))

{{-- The last row of an appliance's shelf list; the inputs line up with the name and load columns above. --}}
<li class="bg-stone-50 px-3 py-2">
    <form method="POST" action="{{ route('households.appliances.shelves.store', [$household->id, $appliance->id]) }}" class="flex items-center gap-3">
        @csrf

        <input
            name="label"
            type="text"
            value="{{ $shelfErrors->any() ? old('label') : '' }}"
            placeholder="New shelf name"
            aria-label="New shelf name for {{ $appliance->name }}"
            required
            class="min-w-0 flex-1 border border-stone-300 bg-white px-2 py-1 text-sm"
        >
        <input
            name="max_load_capacity_g"
            type="number"
            min="0"
            step="0.01"
            placeholder="Max load (g)"
            aria-label="Max load in grams for {{ $appliance->name }}"
            required
            class="w-66 shrink-0 border border-stone-300 bg-white px-2 py-1 text-sm"
        >
        <button type="submit" class="w-12 shrink-0 cursor-pointer bg-stone-800 py-1 text-sm text-white hover:bg-stone-700">Add</button>
    </form>

    @foreach ($shelfErrors->all() as $message)
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @endforeach
</li>
