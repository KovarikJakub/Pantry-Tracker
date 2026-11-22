<?php

use App\Models\Household;
use App\Services\FoodItemPlacer;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    private const MIN_TERM_LENGTH = 2;

    private const MAX_RESULTS = 8;

    public Household $household;

    public string $term = '';

    /**
     * Runs before every update. Route middleware does not protect Livewire's update requests.
     */
    public function hydrate(): void
    {
        $this->authorize('view', $this->household);
    }

    public function updatedTerm(): void
    {
        $this->resetErrorBag();
    }

    #[Computed]
    public function searchTerm(): string
    {
        return trim($this->term);
    }

    #[Computed]
    public function isSearching(): bool
    {
        return mb_strlen($this->searchTerm) >= self::MIN_TERM_LENGTH;
    }

    /**
     * Products of the household's catalogue whose name contains the term.
     */
    #[Computed]
    public function results(): Collection
    {
        if (! $this->isSearching) {
            return collect();
        }

        return $this->household->catalogueProducts()
            ->with('category')
            ->where('name', 'LIKE', '%'.addcslashes($this->searchTerm, '%_\\').'%')
            ->orderBy('name')
            ->limit(self::MAX_RESULTS)
            ->get();
    }

    /**
     * Puts the product on the selected shelf straight away, with its default price and expiry.
     * FoodItemPlacer only accepts shelves of this household, so the client-sent shelf id is safe.
     */
    public function add(FoodItemPlacer $foodItemPlacer, string $productId, string $shelfId): void
    {
        $this->resetErrorBag();

        $product = $this->household->catalogueProducts()->findOrFail($productId);

        $foodItemPlacer->place(
            $this->household,
            $shelfId,
            $product,
            request()->user()->id,
            $product->default_price,
            today()->addDays($product->default_expiry_days),
        );

        $this->reset('term');
        $this->dispatch('food-added', shelfId: $shelfId);
    }
};
?>

<div
    class="max-w-xl"
    data-keeps-shelf-selection
    x-data="{
        open: false,
        highlighted: -1,
        get options() {
            return [...this.$root.querySelectorAll('[role=option]')];
        },
        move(step) {
            this.open = true;

            if (this.options.length === 0) {
                return;
            }

            const start = this.highlighted === -1 && step < 0 ? 0 : this.highlighted;
            this.highlighted = (start + step + this.options.length) % this.options.length;
        },
        selectHighlighted() {
            this.options[this.highlighted]?.click();
        },
        choose(productId) {
            if (this.activeShelfLabel === null) {
                this.needsShelfPrompt = true;
                this.open = false;

                return;
            }

            this.highlighted = -1;
            this.$wire.add(productId, this.activeShelfId);
        },
    }"
    @click.outside="open = false"
    @shelf-selected.window="$refs.input.focus()"
>
    <div class="relative">
        <p class="mb-2 text-sm" role="status">
            <span x-show="activeShelfLabel !== null" class="text-stone-600">Adding to <span class="font-semibold" x-text="activeShelfLabel"></span></span>
            <span
                x-show="activeShelfLabel === null"
                :class="needsShelfPrompt ? 'mt-1.5 text-xs text-[#2F4A3A]/60' : 'text-stone-500'"
            >Select a shelf below to add products to it.</span>
        </p>
        <input
            x-ref="input"
            type="search"
            wire:model.live.debounce.300ms="term"
            placeholder="Search products to add..."
            aria-label="Search products to add to the selected shelf"
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="open"
            autocomplete="off"
            class="w-full max-w-md rounded-xl border border-[#2F4A3A]/25 bg-white px-4 py-2 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/45 shadow-sm transition focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#d78c7d]/40"
            @focus="open = true"
            @input="open = true; highlighted = -1"
            @keydown.arrow-down.prevent="move(1)"
            @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.escape.prevent="open = false"
        >

        @if ($this->isSearching || $errors->has('shelf_id'))
            <div x-show="open" class="mt-1 w-full max-w-md overflow-hidden rounded-xl border border-[#2F4A3A]/20 bg-white shadow-md">
                @error('shelf_id')
                    <p role="alert" class="px-3 py-2 text-red-700">{{ $message }}</p>
                @enderror

                @if ($this->results->isNotEmpty())
                    <ul role="listbox" aria-label="Matching products">
                        @foreach ($this->results as $product)
                            <li
                                wire:key="{{ $product->id }}"
                                role="option"
                                :aria-selected="highlighted === {{ $loop->index }}"
                                :class="highlighted === {{ $loop->index }} && 'bg-stone-200'"
                                class="cursor-pointer px-3 py-1.5"
                                @mouseenter="highlighted = {{ $loop->index }}"
                                @mousedown.prevent
                                @click="choose(@js($product->id))"
                            >
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="min-w-0 truncate font-medium">{{ $product->name }}</span>
                                    <span class="shrink-0 text-xs text-stone-600 tabular-nums">{{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}</span>
                                </div>
                                <div class="flex items-baseline justify-between gap-3 text-xs text-stone-500">
                                    <span class="min-w-0 truncate">{{ $product->category->name }}</span>
                                    <span class="shrink-0">{{ trans_choice('Expires in :count day|Expires in :count days', $product->default_expiry_days) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @elseif ($this->isSearching)
                    <p class="px-3 py-2 text-stone-500">No products found.</p>
                @endif
            </div>
        @endif
    </div>
</div>
