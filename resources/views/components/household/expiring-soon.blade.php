<?php

use App\Models\FoodItem;
use App\Models\Household;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    private const MAX_SHOWN = 5;

    public Household $household;

    /**
     * Runs before every update. Route middleware does not protect Livewire's update requests.
     */
    public function hydrate(): void
    {
        $this->authorize('view', $this->household);
    }

    /**
     * Stored food that is expired or expires within a week, soonest first.
     *
     * @return Collection<int, FoodItem>
     */
    #[Computed]
    public function items(): Collection
    {
        return FoodItem::query()
            ->with(['product', 'shelf'])
            ->where('status', 'stored')
            ->whereDate('expiration_date', '<=', today()->addDays(7))
            ->whereHas('shelf.appliance', fn (Builder $appliances) => $appliances->where('household_id', $this->household->id))
            ->orderBy('expiration_date')
            ->get();
    }

    /**
     * Re-renders with the new food; the computed items are always read fresh.
     */
    #[On('food-added')]
    public function refreshItems(): void {}

    public function maxShown(): int
    {
        return self::MAX_SHOWN;
    }
};
?>

<div>
    @if ($this->items->isNotEmpty())
        <section x-data="{ expanded: false }" aria-labelledby="expiring-soon-title"
                 class="mt-6 rounded-2xl border border-[#2F4A3A]/15 bg-white p-5 shadow-sm">
            <h2 id="expiring-soon-title" class="mb-2 text-base font-semibold text-[#2F4A3A]">
                Expiring soon <span class="font-normal text-[#2F4A3A]/60">({{ $this->items->count() }})</span>
            </h2>

            <ul class="mt-2 space-y-1.5">
                @foreach ($this->items as $item)
                    <li
                        wire:key="{{ $item->id }}"
                        @if ($loop->index >= $this->maxShown()) x-show="expanded" x-cloak @endif
                        class="flex items-center gap-3 text-sm"
                    >
                        <x-food-item.expiry-tag :expiry-date="$item->expiration_date" />
                        <span class="text-[#2F4A3A]">{{ $item->product->name }}</span>
                        <span class="text-[#2F4A3A]/60">{{ $item->shelf->label }}</span>
                    </li>
                @endforeach
            </ul>

            @if ($this->items->count() > $this->maxShown())
                <button
                    type="button"
                    @click="expanded = !expanded"
                    class="mt-2 inline-flex cursor-pointer items-center gap-1 text-sm font-medium text-[#2F4A3A]/70 transition hover:text-[#2F4A3A]"
                >
                    <span x-text="expanded ? 'Show less' : 'and {{ $this->items->count() - $this->maxShown() }} more'"></span>
                    <svg
                        class="size-3.5 transition-transform"
                        :class="expanded && 'rotate-180'"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>
            @endif
        </section>
    @endif
</div>
