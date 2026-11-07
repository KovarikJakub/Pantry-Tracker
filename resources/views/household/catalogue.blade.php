<x-layouts::app :title="$household->name.' catalogue'">
    <h1 class="text-2xl font-semibold">{{ $household->name }} – category catalogue</h1>

    <x-category.tree
        :household="$household"
        :categories-by-parent="$categoriesByParent"
        :products-by-category="$productsByCategory"
        :open-category-ids="$openCategoryIds"
    />

    <x-product.add-dialog
        :household="$household"
        :category-names="$categoriesByParent->flatten(1)->pluck('name', 'id')"
    />

    <a href="{{ route('households.show', $household->id) }}" class="mt-4 inline-block underline">Back to household</a>
</x-layouts::app>
