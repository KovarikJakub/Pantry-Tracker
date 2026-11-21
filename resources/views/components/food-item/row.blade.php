@props(['household','foodItem', 'readOnly' => false])

<tr wire:key="{{ $foodItem->id }}" class="border-t border-stone-200">
    <td class="px-3 py-2">{{ $foodItem->product->name }}</td>
    <td class="px-3 py-2">{{ (float) $foodItem->weight_volume }} {{ $foodItem->product->measurement_unit }}</td>
    <td class="px-3 py-2"><x-food-item.expiry-tag :expiry-date="$foodItem->expiration_date" /></td>
    @unless($readOnly)
        <td class="px-3 py-2 text-right">
        <span
            x-data="{
                item: @js([
                    'url' => route('households.food-items.update', [$household->id, $foodItem->id]),
                    'name' => $foodItem->product->name,
                    'unit' => $foodItem->product->measurement_unit,
                    'maxWeight' => (float) $foodItem->weight_volume,
                ])
            }"
            class="inline"
        >
            <x-food-item.action-button
                type="button"
                label="Eat"
                icon="eat"
                x-on:click="$dispatch('open-resolve-food', { ...item, status: 'consumed' })"
            />
            <x-food-item.action-button
                type="button"
                label="Throw out"
                icon="trash"
                x-on:click="$dispatch('open-resolve-food', { ...item, status: 'thrown_out' })"
            />
        </span>
            <form method="POST" action="{{ route('households.food-items.destroy', [$household->id, $foodItem->id]) }}" class="inline">
                @csrf
                @method('DELETE')
                <x-food-item.action-button label="Remove" icon="remove" />
            </form>
        </td>
    @endunless
</tr>
