<x-household.layout :household="$household" title="Catalogue">
    @error('category_id', 'categoryDelete')
        <p role="alert" class="my-2 text-red-600">{{ $message }}</p>
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
