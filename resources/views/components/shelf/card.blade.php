@props(['household', 'shelf', 'readOnly' => false])

<?php

use App\Models\FoodItem;
use App\Models\Household;
use App\Models\Shelf;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public Household $household;

    public Shelf $shelf;
    public bool $readOnly = false;

    /**
     * Runs before every update. Route middleware does not protect Livewire's update requests.
     */
    public function hydrate(): void
    {
        $this->authorize('view', $this->household);
    }

    /**
     * The food stored on this shelf, soonest expiry first.
     *
     * @return Collection<int, FoodItem>
     */
    #[Computed]
    public function items(): Collection
    {
        return $this->shelf->foodItems()->with('product')->where('status', 'stored')->orderBy('expiration_date')->get();
    }

    /**
     * Every shelf hears the event; only the shelf that got the food re-renders.
     */
    #[On('food-added')]
    public function refreshIfThisShelf(string $shelfId): void
    {
        if ($shelfId !== $this->shelf->id) {
            $this->skipRender();
        }
    }
};
?>

<section
    data-shelf-card
    class="{{ $readOnly ? '' : 'cursor-pointer' }} border border-stone-200 bg-white"
    @unless ($readOnly)
        :class="{
            'ring-2 ring-stone-800': activeShelfId === @js($shelf->id),
            'ring-2 ring-amber-400': needsShelfPrompt,
        }"
    @click="if (! $event.target.closest('button, a, input, select, form')) selectShelf(@js($shelf->id))"
    @endunless
>
    <header class="flex flex-wrap items-center gap-x-3 gap-y-1 bg-stone-100 px-3 py-2">
        <h3 class="font-semibold">{{ $shelf->label }}</h3>
        @unless ($readOnly)
        <button
            type="button"
            class="text-xs underline"
            :aria-pressed="activeShelfId === @js($shelf->id)"
            @click="toggleShelf(@js($shelf->id))"
            x-text="activeShelfId === @js($shelf->id) ? 'Selected' : 'Select'"
        >Select</button>
        <button
            type="button"
            x-data
            class="text-xs underline"
            @click="$dispatch('open-add-food', @js(['id' => $shelf->id, 'label' => $shelf->label]))"
        >+ Add food</button>
        @endunless
        <x-shelf.capacity-bar
            class="ml-auto"
            :current-weight="$this->items->sum('weight_volume')"
            :max-weight="$shelf->max_load_capacity_g"
        />
    </header>

    {{-- Fixed column widths keep the columns of every shelf lined up with each other. --}}
    <table class="w-full table-fixed text-sm">
        <colgroup>
            <col>
            <col class="w-24">
            <col class="w-32">
            @unless($readOnly)
            <col class="w-52">
            @endunless
        </colgroup>
        <tbody>
            @forelse ($this->items as $item)
                <x-food-item.row :household="$household" :food-item="$item" :read-only="$readOnly"/>
            @empty
                <tr class="border-t border-stone-200">
                    <td colspan="4" class="px-3 py-2 text-stone-500">Empty shelf.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
