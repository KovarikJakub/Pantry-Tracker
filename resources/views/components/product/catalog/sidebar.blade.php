@props(['categoriesByParent'])

<nav aria-label="Categories" class="w-56 shrink-0 overflow-y-auto border-r pr-2">
    <button
        type="button"
        class="w-full px-1 py-0.5 text-left hover:bg-stone-100"
        :class="selectedCategoryId === null && search.trim() === '' && 'bg-stone-200 font-medium'"
        @click="select(null, null)"
    >All products</button>

    <ul>
        @foreach ($categoriesByParent->get('', collect()) as $category)
            <x-product.catalog.category-node :category="$category" :categories-by-parent="$categoriesByParent" />
        @endforeach
    </ul>
</nav>
