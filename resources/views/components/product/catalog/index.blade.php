@props(['categoriesByParent', 'products', 'productCategoryIds'])

{{--
    File-explorer-like product browser: categories on the left, products of the selected category (and its
    subcategories) on the right. A non-empty search looks through all products, ignoring the selected category.
    Everything is rendered once; Alpine only shows and hides product cards.
--}}

@php
    /** @var array<string, array{name: string, categoryIds: list<string>}> $entries product id => filter data */
    $entries = $products->mapWithKeys(fn ($product) => [
        $product->id => [
            'name' => $product->name,
            'categoryIds' => $productCategoryIds->get($product->id, []),
        ],
    ]);
@endphp

<div
    x-data="{
        products: @js($entries),
        selectedCategoryId: null,
        selectedCategoryName: null,
        search: '',
        get searchTerm() {
            return this.search.trim().toLowerCase();
        },
        get heading() {
            if (this.searchTerm !== '') {
                return `Results for “${this.search.trim()}”`;
            }

            return this.selectedCategoryName ?? 'All products';
        },
        get hasResults() {
            return Object.keys(this.products).some((id) => this.isVisible(id));
        },
        isVisible(id) {
            const product = this.products[id];

            if (this.searchTerm !== '') {
                return product.name.toLowerCase().includes(this.searchTerm);
            }

            return this.selectedCategoryId === null || product.categoryIds.includes(this.selectedCategoryId);
        },
        select(id, name) {
            this.selectedCategoryId = id;
            this.selectedCategoryName = name;
            this.search = '';
        },
    }"
    @open-add-food.window="select(null, null)"
    class="flex min-h-0 flex-1 flex-col gap-3"
>
    <x-product.catalog.search />

    <div class="flex min-h-0 flex-1 gap-4">
        <x-product.catalog.sidebar :categories-by-parent="$categoriesByParent" />
        <x-product.catalog.results :products="$products" />
    </div>
</div>
