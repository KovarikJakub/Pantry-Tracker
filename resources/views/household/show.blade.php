<x-household.layout :household="$household">
    @if (session('food_item_status'))
        <p class="mt-4">{{ session('food_item_status') }}</p>
    @endif

    <livewire:household.expiring-soon :household="$household->withoutRelations()" />

    <x-food-item.add-dialog
        :household="$household"
        :shelf-labels="$household->appliances->flatMap->shelves->pluck('label', 'id')"
        :categories-by-parent="$catalogueCategoriesByParent"
        :products="$catalogueProducts"
        :product-category-ids="$productCategoryIds"
    />

    <x-shelf.selection :household="$household">
        {{-- Hidden without shelves, as there is nowhere to add food. Sticky, so the bar stays reachable while picking a shelf further down the page. --}}
        @if ($household->appliances->contains(fn ($appliance) => $appliance->shelves->isNotEmpty()))
            <div class="sticky top-0 z-30 mt-6 bg-stone-50 py-2">
                <livewire:product.searchbar :household="$household->withoutRelations()" />
            </div>
        @endif

        <x-appliance.list :household="$household" />
    </x-shelf.selection>
</x-household.layout>
