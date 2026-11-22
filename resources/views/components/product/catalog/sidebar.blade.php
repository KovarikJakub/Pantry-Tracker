@props(['categoriesByParent'])

<nav aria-label="Categories" class="w-56 shrink-0 overflow-y-auto border-r border-[#2F4A3A]/10 pr-3">
    <button
        type="button"
        class="mb-1 flex w-full cursor-pointer items-center rounded-lg px-2.5 py-1.5 text-left text-sm text-[#2F4A3A] transition hover:bg-[#F4F6F4]"
        :class="selectedCategoryId === null && search.trim() === '' && 'bg-[#EFEBF6] font-medium text-[#2F4A3A]'"
        @click="select(null, null)"
    >
        All products
    </button>

    <ul class="space-y-0.5">
        @foreach ($categoriesByParent->get('', collect()) as $category)
            <x-product.catalog.category-node :category="$category" :categories-by-parent="$categoriesByParent" />
        @endforeach
    </ul>
</nav>
