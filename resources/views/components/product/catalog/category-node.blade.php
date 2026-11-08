@props(['category', 'categoriesByParent'])

{{--
    Clicking the name selects the category and expands it; the arrow only expands or collapses.
--}}

@php
    $subcategories = $categoriesByParent->get($category->id, collect());
    $rowClasses = 'flex w-full cursor-pointer items-center gap-1 px-1 py-0.5 text-left hover:bg-stone-100';
@endphp

<li>
    @if ($subcategories->isEmpty())
        <button
            type="button"
            class="{{ $rowClasses }} pl-5"
            :class="selectedCategoryId === @js($category->id) && search.trim() === '' && 'bg-stone-200 font-medium'"
            @click="select(@js($category->id), @js($category->name))"
        >📁 {{ $category->name }}</button>
    @else
        <details>
            <summary
                class="{{ $rowClasses }} list-none [&::-webkit-details-marker]:hidden"
                :class="selectedCategoryId === @js($category->id) && search.trim() === '' && 'bg-stone-200 font-medium'"
                @click.prevent="select(@js($category->id), @js($category->name)); $el.parentElement.open = true"
            >
                <span
                    class="inline-block w-3 text-stone-500 [[open]>summary>&]:rotate-90"
                    @click.stop.prevent="$el.closest('details').open = ! $el.closest('details').open"
                >▸</span>
                📁 {{ $category->name }}
            </summary>

            <ul class="ml-3 border-l border-stone-200 pl-1">
                @foreach ($subcategories as $subcategory)
                    <x-product.catalog.category-node :category="$subcategory" :categories-by-parent="$categoriesByParent" />
                @endforeach
            </ul>
        </details>
    @endif
</li>
