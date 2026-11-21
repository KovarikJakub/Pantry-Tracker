@props(['household','foodItem', 'readOnly' => false])

<tr wire:key="{{ $foodItem->id }}" class="border-t border-stone-200">
    <td class="px-3 py-2">{{ $foodItem->product->name }}</td>
    <td class="px-3 py-2">{{ (float) $foodItem->weight_volume }} {{ $foodItem->product->measurement_unit }}</td>
    <td class="px-3 py-2"><x-food-item.expiry-tag :expiry-date="$foodItem->expiration_date" /></td>
    @unless($readOnly)
    <td class="px-3 py-2 text-right">
        <form method="POST" action="{{ route('households.food-items.update', [$household->id, $foodItem->id]) }}" class="inline">
            @csrf
            @method('PATCH')
            <x-food-item.action-button label="Eat" icon="eat" name="status" value="consumed" />
            <x-food-item.action-button label="Throw out" icon="trash" name="status" value="thrown_out" />
        </form>
        <form method="POST" action="{{ route('households.food-items.destroy', [$household->id, $foodItem->id]) }}" class="inline">
            @csrf
            @method('DELETE')
            <x-food-item.action-button label="Remove" icon="remove" />
        </form>
    </td>
    @endunless
</tr>
