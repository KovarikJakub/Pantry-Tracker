<x-household.layout :household="$household" title="Catalogue">
    @error('category_id', 'categoryDelete')
    <p role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
        {{ $message }}
    </p>
    @enderror

    <x-category.tree
        :household="$household"
        :categories-by-parent="$categoriesByParent"
        :products-by-category="$productsByCategory"
        :open-category-ids="$openCategoryIds"
    />

    <x-product.edit-dialog
    />

    <x-product.add-dialog
        :household="$household"
        :category-names="$categoriesByParent->flatten(1)->pluck('name', 'id')"
    />
</x-household.layout>
