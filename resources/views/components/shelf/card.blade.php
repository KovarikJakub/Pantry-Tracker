@props(['household', 'shelf'])

@php($items = $shelf->foodItems->where('status', 'stored'))

<table class="mt-2 w-full max-w-xl border">
    <thead>
        <tr class="bg-stone-100">
            <th colspan="3" class="border p-2 text-left">
                {{ $shelf->label }}
                <button
                    type="button"
                    x-data
                    class="ml-2 text-xs font-normal underline"
                    @click="$dispatch('open-add-food', @js(['id' => $shelf->id, 'label' => $shelf->label]))"
                >+ Add food</button>
                <x-shelf.capacity-bar
                    class="float-right"
                    :current-weight="$items->sum('weight_volume')"
                    :max-weight="$shelf->max_load_capacity_g"
                />
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $item)
            <x-food-item.row :household="$household" :food-item="$item" />
        @empty
            <tr>
                <td colspan="3" class="border p-2 text-stone-500">Empty shelf.</td>
            </tr>
        @endforelse
    </tbody>
</table>
