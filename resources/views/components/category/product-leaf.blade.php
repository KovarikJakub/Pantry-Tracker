@props(['household', 'product'])

<li class="flex items-center gap-2 px-1 py-0.5 pl-6 hover:bg-stone-100">
    <span>📄</span>
    <span>{{ $product->name }}</span>
    <span class="text-xs text-stone-500">{{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}</span>
    <!--
    -- Edit – this uses one shared modal (for all the products) on the page and uses Alpine.js
    -- to pass the clicked product's data into it.
    -->
    <button
        type="button"
        x-data
        class="underline"
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
    >Edit</button>
    <form method="POST" action="{{ route('households.products.destroy', [$household->id, $product->id]) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
</li>
