@props(['household', 'category', 'categoriesByParent', 'productsByCategory', 'openCategoryIds' => []])

@php
    $subcategories = $categoriesByParent->get($category->id, collect());
    $products = $productsByCategory->get($category->id, collect());
    $childCount = $subcategories->count() + $products->count();
    $hasFailedAdd = old('parent_id') === $category->id && $errors->category->any();
@endphp

<li x-data="{ adding: @js($hasFailedAdd) }">
    <details @if (in_array($category->id, $openCategoryIds, true)) open @endif>
        <summary class="group/row flex cursor-pointer list-none items-center gap-2 px-1 py-0.5 hover:bg-stone-100 [&::-webkit-details-marker]:hidden">
            <span class="inline-block w-3 text-stone-500 [[open]>summary>&]:rotate-90">▸</span>
            <span>📁</span>
            <span class="font-medium">{{ $category->name }}</span>
            <span class="text-xs text-stone-500">{{ $childCount === 0 ? 'empty' : $childCount }}</span>
            <button
                type="button"
                class="invisible ml-auto text-xs underline group-hover/row:visible focus:visible"
                @click.prevent="adding = true; $el.closest('details').open = true"
            >+ Add subcategory</button>
            <button
                type="button"
                class="invisible text-xs underline group-hover/row:visible focus:visible"
                @click.prevent="$dispatch('open-add-product', @js(['id' => $category->id, 'name' => $category->name]))"
            >+ Add product</button>
        </summary>

        <ul class="ml-3 border-l border-stone-200 pl-3">
            <template x-if="adding">
                <x-category.add-form :household="$household" :parent="$category" />
            </template>

            @foreach ($subcategories as $subcategory)
                <x-category.node
                    :household="$household"
                    :category="$subcategory"
                    :categories-by-parent="$categoriesByParent"
                    :products-by-category="$productsByCategory"
                    :open-category-ids="$openCategoryIds"
                />
            @endforeach

            @foreach ($products as $product)
                <x-category.product-leaf :household="$household" :product="$product" />
            @endforeach
        </ul>
    </details>
</li>
