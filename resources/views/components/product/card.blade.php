@props(['product'])

<div {{ $attributes->merge(['class' => 'flex flex-col justify-between rounded-xl border border-[#2F4A3A]/15 bg-white p-3.5 shadow-xs transition hover:border-[#2F4A3A]/35']) }}>
    <p class="inline-block self-start rounded-full bg-[#EFEBF6] px-2 py-0.5 text-xs font-medium text-[#6E5D8F]">{{ $product->category->name }}</p>
    <p class="mt-1.5 font-semibold text-[#2F4A3A]">{{ $product->name }}</p>
    <p class="text-xs text-[#2F4A3A]/65">{{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}</p>
    <p class="text-xs text-[#2F4A3A]/65">{{ (float) $product->calories_per_100g }} kcal / 100 {{ $product->measurement_unit }}</p>
    <p class="text-xs text-[#2F4A3A]/65">Default price: {{ (float) $product->default_price }}</p>
    <button type="submit" form="add-food-item" name="product_id" value="{{ $product->id }}" class="mt-3 w-full cursor-pointer rounded-full bg-[#2F4A3A] px-3 py-1.5 text-xs font-medium text-white transition hover:bg-[#2F4A3A]/90"
    >Add
    </button>
</div>
