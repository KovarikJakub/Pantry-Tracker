@props(['product'])

<div {{ $attributes->merge(['class' => 'border bg-white p-3 text-sm']) }}>
    <p class="text-xs text-stone-500">{{ $product->category->name }}</p>
    <p class="font-semibold">{{ $product->name }}</p>
    <p>{{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}</p>
    <p class="text-stone-500">{{ (float) $product->calories_per_100g }} kcal / 100</p>
    <button type="submit" form="add-food-item" name="product_id" value="{{ $product->id }}" class="mt-2 bg-stone-800 px-2 py-1 text-white">Add</button>
</div>
