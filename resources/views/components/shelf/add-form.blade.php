@props(['household', 'appliance'])

@php($shelfErrors = $errors->getBag('shelf_'.$appliance->id))

<form method="POST" action="{{ route('households.shelves.store', [$household->id, $appliance->id]) }}" class="mt-2 flex flex-wrap items-end gap-2">
    @csrf

    <input name="label" type="text" placeholder="Shelf label" required class="border p-2">
    <input name="max_load_capacity_g" type="number" min="0" step="0.01" placeholder="Max load (g)" required class="border p-2">
    <button type="submit" class="bg-stone-800 p-2 text-white">Add shelf</button>
</form>

@foreach ($shelfErrors->all() as $message)
    <p class="text-red-700">{{ $message }}</p>
@endforeach
