@props(['household','foodItem'])

<tr>
    <td class="border p-2">{{ $foodItem->product->name }}</td>
    <td class="border p-2">{{ (float) $foodItem->weight_volume }} {{ $foodItem->product->measurement_unit }}</td>
    <td class="border p-2">{{ $foodItem->expiration_date->format('Y-m-d') }}</td>
    <td class="border p-2">
        <form method="POST" action="{{ route('household.food-items.remove', [$household->id, $foodItem->id]) }}">
            <button type="submit" name="action" value="consumed">Eat</button>
            <button type="submit" name="action" value="thrown_out">Throw out</button>
            <button type="submit" name="action" value="removed">Remove</button>
        </form>
    </td>
</tr>
