@props(['household', 'shelf'])

@php($items = $shelf->foodItems->where('status', 'stored')->sortBy('expiration_date'))

<section class="border border-stone-200 bg-white">
    <header class="flex flex-wrap items-center gap-x-3 gap-y-1 bg-stone-100 px-3 py-2">
        <h3 class="font-semibold">{{ $shelf->label }}</h3>
        <button
            type="button"
            x-data
            class="text-xs underline"
            @click="$dispatch('open-add-food', @js(['id' => $shelf->id, 'label' => $shelf->label]))"
        >+ Add food</button>
        <x-shelf.capacity-bar
            class="ml-auto"
            :current-weight="$items->sum('weight_volume')"
            :max-weight="$shelf->max_load_capacity_g"
        />
    </header>

    {{-- Fixed column widths keep the columns of every shelf lined up with each other. --}}
    <table class="w-full table-fixed text-sm">
        <colgroup>
            <col>
            <col class="w-24">
            <col class="w-32">
            <col class="w-52">
        </colgroup>
        <tbody>
            @forelse ($items as $item)
                <x-food-item.row :household="$household" :food-item="$item" />
            @empty
                <tr class="border-t border-stone-200">
                    <td colspan="4" class="px-3 py-2 text-stone-500">Empty shelf.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
