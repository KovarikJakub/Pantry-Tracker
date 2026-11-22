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
            class="min-w-0 flex-1 rounded-xl border border-[#2F4A3A]/20 pl-3 pr-2 py-1 text-sm"
        >
        <input
            name="max_load_capacity_g"
            type="number"
            min="0"
            step="1000"
            placeholder="Max load (g)"
            aria-label="Max load in grams for {{ $appliance->name }}"
            required
            class="rounded-xl border border-[#2F4A3A]/20 pl-3 pr-2 py-1 text-sm"
        >
        <button type="submit" class="rounded-full bg-[#2F4A3A] px-3 py-1.5 text-xs font-medium text-white">Add</button>
    </form>

    @foreach ($shelfErrors->all() as $message)
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @endforeach
</li>
