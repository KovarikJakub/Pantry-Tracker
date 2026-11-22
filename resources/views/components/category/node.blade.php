@props(['category', 'categoriesByParent', 'openCategoryIds' => []])

@php
    $subcategories = $categoriesByParent->get($category->id, collect());
    $rowClasses = 'flex w-full cursor-pointer items-center gap-1.5 rounded-lg px-2 py-1.5 text-left text-sm text-[#2F4A3A] transition hover:bg-[#F4F6F4]';
@endphp

<li>
    @if ($subcategories->isEmpty())
        <button
            type="button"
            class="{{ $rowClasses }} pl-7"
            :class="selectedCategoryId === @js($category->id) && searchTerm === '' && 'bg-[#EFEBF6] font-medium text-[#2F4A3A]'"
            @click="select(@js($category->id), @js($category->name))"
        >
            <span class="truncate">{{ $category->name }}</span>
        </button>
    @else
        <details @if (in_array($category->id, $openCategoryIds, true)) open @endif>
            <summary
                class="{{ $rowClasses }} list-none [&::-webkit-details-marker]:hidden"
                :class="selectedCategoryId === @js($category->id) && searchTerm === '' && 'bg-[#EFEBF6] font-medium text-[#2F4A3A]'"
                @click.prevent="select(@js($category->id), @js($category->name)); $el.parentElement.open = true"
            >
                <span
                    class="flex size-4 shrink-0 items-center justify-center text-[#2F4A3A]/50 transition-transform [[open]>summary>&]:rotate-90"
                    @click.stop.prevent="$el.closest('details').open = ! $el.closest('details').open"
                >
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </span>
                <span class="truncate">{{ $category->name }}</span>
            </summary>

            <ul class="ml-3.5 mt-0.5 space-y-0.5 border-l border-[#2F4A3A]/12 pl-2">
                @foreach ($subcategories as $subcategory)
                    <x-category.node
                        :category="$subcategory"
                        :categories-by-parent="$categoriesByParent"
                        :open-category-ids="$openCategoryIds"
                    />
                @endforeach
            </ul>
        </details>
    @endif
</li>
