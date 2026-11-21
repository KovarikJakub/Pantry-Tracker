@props(['household','foodItem', 'readOnly' => false])

<tr class="border-t border-stone-200">
    <td class="px-3 py-2">{{ $foodItem->product->name }}</td>
    <td class="px-3 py-2">{{ (float) $foodItem->weight_volume }} {{ $foodItem->product->measurement_unit }}</td>
    <td class="px-3 py-2"><x-food-item.expiry-tag :expiry-date="$foodItem->expiration_date" /></td>
    @unless($readOnly)
    <td class="px-3 py-2 text-right">
        <form method="POST" action="{{ route('households.food-items.update', [$household->id, $foodItem->id]) }}" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit" name="status" value="consumed">Eat</button>
            <button type="submit" name="status" value="thrown_out">Throw out</button>
        </form>
        <form method="POST" action="{{ route('households.food-items.destroy', [$household->id, $foodItem->id]) }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit">Remove</button>
        </form>
    </td>
    @endunless
</tr>
