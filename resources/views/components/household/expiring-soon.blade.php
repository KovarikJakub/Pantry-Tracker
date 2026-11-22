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
        <section aria-labelledby="expiring-soon-title">
            <h2 id="expiring-soon-title" class="font-semibold">
                Expiring soon <span class="font-normal text-stone-600">({{ $this->items->count() }})</span>
            </h2>

            <ul class="mt-2 space-y-1">
                @foreach ($this->items->take($this->maxShown()) as $item)
                    <li wire:key="{{ $item->id }}" class="flex items-center gap-3">
                        <x-food-item.expiry-tag :expiry-date="$item->expiration_date" />
                        <span>{{ $item->product->name }}</span>
                        <span class="text-stone-500">{{ $item->shelf->label }}</span>
                    </li>
                @endforeach
            </ul>

            @if ($this->items->count() > $this->maxShown())
                <p class="mt-2 text-stone-600">and {{ $this->items->count() - $this->maxShown() }} more</p>
            @endif
        </section>
    @endif
</div>
