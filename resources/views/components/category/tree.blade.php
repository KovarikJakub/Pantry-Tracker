@props(['household', 'categoriesByParent', 'productsByCategory', 'openCategoryIds' => []])

@php
    $topLevelCategories = $categoriesByParent->get('', collect());
    $categoriesById = $categoriesByParent->flatten(1)->keyBy('id');

    $allProducts = $productsByCategory
        ->flatten(1)
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values();

    // Build ancestor category IDs for each product so clicking a parent category shows its subcategories' products too.
    $entries = $allProducts->mapWithKeys(function ($product) use ($categoriesById) {
        $ids = [];
        $currentId = $product->category_id;
        while ($currentId) {
            $ids[] = $currentId;
            $currentId = $categoriesById->get($currentId)?->parent_id;
        }

        return [
            $product->id => [
                'name' => $product->name,
                'categoryIds' => $ids,
            ],
        ];
    });

    // Metadata for category actions in the right-hand header.
    $categoryMeta = $categoriesById->mapWithKeys(fn ($cat) => [
        $cat->id => [
            'id' => $cat->id,
            'name' => $cat->name,
            'isEmpty' => $categoriesByParent->get($cat->id, collect())->isEmpty()
                && $productsByCategory->get($cat->id, collect())->isEmpty(),
            'deleteUrl' => route('households.categories.destroy', [$household->id, $cat->id]),
        ],
    ]);

    $failedParentId = $errors->category->any() ? old('parent_id') : null;
    $initialCategoryId = $failedParentId ?: (collect($openCategoryIds)->last() ?? null);
    $initialCategoryName = $initialCategoryId ? $categoriesById->get($initialCategoryId)?->name : null;
@endphp

@if ($topLevelCategories->isEmpty())
    <p class="mt-6 text-sm text-[#2F4A3A]/60">The catalogue has no categories.</p>
@else
    <div
        x-data="{
            products: @js($entries),
            categories: @js($categoryMeta),
            selectedCategoryId: @js($initialCategoryId),
            selectedCategoryName: @js($initialCategoryName),
            search: '',
            addingSubcategory: @js((bool) $failedParentId),
            get searchTerm() {
                return this.search.trim().toLowerCase();
            },
            get selectedCategory() {
                return this.selectedCategoryId ? this.categories[this.selectedCategoryId] : null;
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
                this.addingSubcategory = false;
            },
        }"
        class="mt-6 rounded-2xl border border-[#2F4A3A]/15 bg-white p-5 shadow-sm"
    >
        {{-- Top Searchbar --}}
        <div class="relative mb-5">
            <svg
                aria-hidden="true"
                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-[#2F4A3A]/45"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>

            <input
                type="search"
                x-model="search"
                placeholder="Search all products…"
                aria-label="Search all products by name"
                class="w-full rounded-xl border border-[#2F4A3A]/20 bg-white py-2 pl-10 pr-4 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
            >
        </div>

        {{-- Two-column layout: Sidebar + Product Grid --}}
        <div class="flex flex-col gap-6 md:flex-row">
            {{-- Left Category Sidebar --}}
            <nav aria-label="Categories" class="w-full shrink-0 border-b border-[#2F4A3A]/10 pb-4 md:w-56 md:border-b-0 md:border-r md:pb-0 md:pr-4">
                <button
                    type="button"
                    class="mb-1 flex w-full cursor-pointer items-center rounded-lg px-2.5 py-1.5 text-left text-sm text-[#2F4A3A] transition hover:bg-[#F4F6F4]"
                    :class="selectedCategoryId === null && searchTerm === '' && 'bg-[#EFEBF6] font-medium text-[#2F4A3A]'"
                    @click="select(null, null)"
                >
                    All products
                </button>

                <ul class="space-y-0.5">
                    @foreach ($topLevelCategories as $category)
                        <x-category.node
                            :category="$category"
                            :categories-by-parent="$categoriesByParent"
                            :open-category-ids="$openCategoryIds"
                        />
                    @endforeach
                </ul>
            </nav>

            {{-- Right Main Area --}}
            <section aria-live="polite" class="min-w-0 flex-1">
                {{-- Header + Category Actions --}}
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-[#2F4A3A]" x-text="heading"></h2>

                    <div x-show="selectedCategory && searchTerm === ''" x-cloak class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="addingSubcategory = !addingSubcategory"
                            class="cursor-pointer rounded-full border border-[#2F4A3A]/25 bg-white px-3 py-1 text-xs font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/60"
                        >
                            + Subcategory
                        </button>

                        <button
                            type="button"
                            @click="$dispatch('open-add-product', { id: selectedCategory.id, name: selectedCategory.name })"
                            class="cursor-pointer rounded-full bg-[#2F4A3A] px-3 py-1 text-xs font-medium text-white transition hover:bg-[#2F4A3A]/90"
                        >
                            + Add product
                        </button>

                        <form
                            x-show="selectedCategory?.isEmpty"
                            method="POST"
                            :action="selectedCategory?.deleteUrl"
                            @submit="if (!confirm(`Delete category “${selectedCategory.name}”?`)) $event.preventDefault()"
                            class="contents"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="cursor-pointer rounded-full border border-red-600/30 bg-white px-3 py-1 text-xs font-medium text-red-700 transition hover:border-red-600 hover:bg-red-50"
                            >
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Inline "Add Subcategory" form --}}
                <div x-show="addingSubcategory && selectedCategory" x-cloak class="mb-4 rounded-xl border border-[#2F4A3A]/15 bg-[#F4F6F4] p-3">
                    <form
                        method="POST"
                        action="{{ route('households.categories.store', $household->id) }}"
                        class="flex flex-wrap items-center gap-2"
                        @keydown.escape="addingSubcategory = false"
                    >
                        @csrf
                        <input type="hidden" name="parent_id" :value="selectedCategoryId">

                        <input
                            name="name"
                            value="{{ old('name') }}"
                            :placeholder="`New subcategory of ${selectedCategoryName}`"
                            required
                            maxlength="255"
                            class="min-w-48 flex-1 rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
                        >

                        <button
                            type="submit"
                            class="cursor-pointer rounded-full bg-[#2F4A3A] px-4 py-1.5 text-xs font-medium text-white transition hover:bg-[#2F4A3A]/90"
                        >
                            Add
                        </button>

                        <button
                            type="button"
                            @click="addingSubcategory = false"
                            class="cursor-pointer rounded-full px-3 py-1.5 text-xs font-medium text-[#2F4A3A]/70 transition hover:bg-white hover:text-[#2F4A3A]"
                        >
                            Cancel
                        </button>
                    </form>

                    @error('name', 'category')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Product Card Grid --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($allProducts as $product)
                        @php($categoryName = $categoriesById->get($product->category_id)?->name)
                        <article
                            x-show="isVisible('{{ $product->id }}')"
                            class="flex flex-col justify-between rounded-xl border border-[#2F4A3A]/15 bg-white p-4 shadow-xs transition hover:border-[#2F4A3A]/35"
                        >
                            <div>
                                @if ($categoryName)
                                    <span class="inline-block rounded-full bg-[#EFEBF6] px-2.5 py-0.5 text-xs font-medium text-[#6E5D8F]">
                                        {{ $categoryName }}
                                    </span>
                                @endif

                                <h3 class="mt-2 font-semibold text-[#2F4A3A]">{{ $product->name }}</h3>

                                <div class="mt-1 space-y-0.5 text-xs text-[#2F4A3A]/65">
                                    <p>{{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}</p>
                                    <p>{{ (float) $product->calories_per_100g }} kcal / 100 {{ $product->measurement_unit }}</p>
                                    <p>Default price: {{ (float) $product->default_price }} Kč</p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <button
                                    type="button"
                                    class="flex-1 cursor-pointer rounded-full bg-[#2F4A3A] px-3 py-1.5 text-xs font-medium text-white transition hover:bg-[#2F4A3A]/90"
                                    @click="$dispatch('open-edit-product', @js([
                                        'url' => route('households.products.update', [$household->id, $product->id]),
                                        'name' => $product->name,
                                        'barcode' => $product->barcode,
                                        'default_size_g_ml' => (float) $product->default_size_g_ml,
                                        'measurement_unit' => $product->measurement_unit,
                                        'default_price' => $product->default_price,
                                        'default_expiry_days' => $product->default_expiry_days,
                                        'calories_per_100g' => $product->calories_per_100g,
                                        'protein_per_100g' => $product->protein_per_100g,
                                        'fat_per_100g' => $product->fat_per_100g,
                                        'carbs_per_100g' => $product->carbs_per_100g,
                                        'fiber_per_100g' => $product->fiber_per_100g,
                                    ]))"
                                >
                                    Edit
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('households.products.destroy', [$household->id, $product->id]) }}"
                                    onsubmit="return confirm(@js('Delete product “'.$product->name.'”?'))"
                                    class="contents"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="cursor-pointer rounded-full border border-red-600/30 bg-white px-3 py-1.5 text-xs font-medium text-red-700 transition hover:border-red-600 hover:bg-red-50"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach

                    <p x-show="!hasResults" x-cloak class="col-span-full py-10 text-center text-sm text-[#2F4A3A]/50">
                        No products in this category yet.
                    </p>
                </div>
            </section>
        </div>
    </div>
@endif
