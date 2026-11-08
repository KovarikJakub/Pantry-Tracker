@props(['household','foodItem'])

<tr>
    <td class="border p-2">{{ $foodItem->product->name }}</td>
    <td class="border p-2">{{ (float) $foodItem->weight_volume }} {{ $foodItem->product->measurement_unit }}</td>
    <td class="border p-2"><x-food-item.expiry-tag :expiry-date="$foodItem->expiration_date" /></td>
    <td class="border p-2">
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
</tr>
