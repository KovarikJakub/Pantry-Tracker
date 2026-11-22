@props(['household', 'product'])

<li class="group/leaf flex items-center gap-3 rounded-lg px-2.5 py-1.5 text-sm text-[#2F4A3A] transition hover:bg-[#F4F6F4]">
    <span aria-hidden="true" class="ml-1 size-1.5 shrink-0 rounded-full bg-[#2F4A3A]/25"></span>

    <span class="min-w-0 flex-1 truncate font-medium text-[#2F4A3A]">
        {{ $product->name }}
    </span>

    <span class="shrink-0 text-xs tabular-nums text-[#2F4A3A]/55">
        {{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}
    </span>

    <div class="flex shrink-0 items-center gap-1.5">
        <button
            type="button"
            x-data
            class="cursor-pointer rounded-full border border-[#2F4A3A]/20 bg-white px-2.5 py-0.5 text-xs font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/60"
            @click="$dispatch('open-edit-product', @js([
                'url' => route('households.products.update', [$household->id, $product->id]),
                'name' => $product->name,
                'barcode' => $product->barcode,
                'default_size_g_ml' => (float) $product->default_size_g_ml,
                'measurement_unit' => $product->measurement_unit,
                'default_price' => $product->default_price,
                'calories_per_100g' => $product->calories_per_100g,
                'protein_per_100g' => $product->protein_per_100g,
                'fat_per_100g' => $product->fat_per_100g,
                'carbs_per_100g' => $product->carbs_per_100g,
                'fiber_per_100g' => $product->fiber_per_100g,
            ]))"
        >
            Edit
        </button>

        <form method="POST" action="{{ route('households.products.destroy', [$household->id, $product->id]) }}" class="contents">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="cursor-pointer rounded-full border border-red-600/30 bg-white px-2.5 py-0.5 text-xs font-medium text-red-700 transition hover:border-red-600 hover:bg-red-50"
            >
                Delete
            </button>
        </form>
    </div>
</li>
