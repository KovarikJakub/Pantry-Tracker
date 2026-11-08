<x-household.layout :household="$household">
    @if (session('food_item_status'))
        <p class="mt-4">{{ session('food_item_status') }}</p>
    @endif

    <x-food-item.add-dialog
        :household="$household"
        :shelf-labels="$household->appliances->flatMap->shelves->pluck('label', 'id')"
        :categories-by-parent="$catalogueCategoriesByParent"
        :products="$catalogueProducts"
        :product-category-ids="$productCategoryIds"
    />

    <x-appliance.list :household="$household" />
</x-household.layout>
