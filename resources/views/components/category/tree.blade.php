@props(['household', 'categoriesByParent', 'productsByCategory', 'openCategoryIds' => []])

@php($topLevelCategories = $categoriesByParent->get('', collect()))

@if ($topLevelCategories->isEmpty())
    <p class="mt-6">The catalogue has no categories.</p>
@else
    <ul class="mt-6 border bg-white p-3 text-sm">
        @foreach ($topLevelCategories as $category)
            <x-category.node
                :household="$household"
                :category="$category"
                :categories-by-parent="$categoriesByParent"
                :products-by-category="$productsByCategory"
                :open-category-ids="$openCategoryIds"
            />
        @endforeach
    </ul>
@endif
