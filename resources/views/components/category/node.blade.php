@props(['category', 'categoriesByParent', 'productsByCategory'])

@php
    $subcategories = $categoriesByParent->get($category->id, collect());
    $products = $productsByCategory->get($category->id, collect());
    $childCount = $subcategories->count() + $products->count();
@endphp

<li>
    <details>
        <summary class="flex cursor-pointer list-none items-center gap-2 px-1 py-0.5 hover:bg-stone-100 [&::-webkit-details-marker]:hidden">
            <span class="inline-block w-3 text-stone-500 [[open]>summary>&]:rotate-90">▸</span>
            <span>📁</span>
            <span class="font-medium">{{ $category->name }}</span>
            <span class="text-xs text-stone-500">{{ $childCount === 0 ? 'empty' : $childCount }}</span>
        </summary>

        @if ($childCount > 0)
            <ul class="ml-3 border-l border-stone-200 pl-3">
                @foreach ($subcategories as $subcategory)
                    <x-category.node :category="$subcategory" :categories-by-parent="$categoriesByParent" :products-by-category="$productsByCategory" />
                @endforeach

                @foreach ($products as $product)
                    <x-category.product-leaf :product="$product" />
                @endforeach
            </ul>
        @endif
    </details>
</li>
