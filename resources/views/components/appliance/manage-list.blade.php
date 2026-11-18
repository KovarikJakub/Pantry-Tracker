<?php

use App\Models\Appliance;
use App\Models\Household;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Household $household;

    public ?string $status = null;

    /** The appliance the status message belongs to; null shows it above the list. */
    public ?string $statusApplianceId = null;

    /**
     * Runs before every action. Route middleware does not protect Livewire's update requests.
     */
    public function hydrate(): void
    {
        $this->authorize('manage', $this->household);
    }

    /**
     * The household's appliances with their shelves; each shelf has the weight of its stored food
     * as stored_weight.
     *
     * @return Collection<int, Appliance>
     */
    #[Computed]
    public function appliances(): Collection
    {
        return $this->household->appliances()
            ->with(['shelves' => fn ($shelves) => $shelves->withSum(
                ['foodItems as stored_weight' => fn ($items) => $items->where('status', 'stored')],
                'weight_volume',
            )])
            ->get();
    }

    /**
     * Removes a shelf from an appliance.
     */
    public function removeShelf(string $applianceId, string $shelfId): void
    {
        $appliance = $this->household->appliances()->findOrFail($applianceId);
        $shelf = $appliance->shelves()->findOrFail($shelfId);

        $this->statusApplianceId = $appliance->id;

        $hasStoredFood = ! $shelf->foodItems->isEmpty();
        if($hasStoredFood){
            $this->status = "{$shelf->label} still contains food. Remove the food first.";
            return;
        }

        $shelf->delete();
        $this->status = "{$shelf->label} was removed.";
    }

    /**
     * Removes an appliance with its shelves. Refused while food is still stored in it.
     */
    public function removeAppliance(string $applianceId): void
    {
        $appliance = $this->household->appliances()->findOrFail($applianceId);

        $this->statusApplianceId = $appliance->id;

        $hasStoredFood = $appliance->shelves()
            ->whereHas('foodItems', fn ($query) => $query->where('status', 'stored'))
            ->exists();

        if ($hasStoredFood) {
            $this->status = "{$appliance->name} still contains food. Remove the food first.";

            return;
        }

        $appliance->delete();

        $this->status = "{$appliance->name} was removed.";
        $this->statusApplianceId = null;
        unset($this->appliances);
    }
};
?>

{{-- Appliances with their shelves and the "Add shelf" form, for the Appliances tab. --}}
<div>
    @if ($status && $statusApplianceId === null)
        <p role="status" class="mt-4">{{ $status }}</p>
    @endif

    @if ($this->appliances->isEmpty())
        <p class="mt-4">There are no appliances in this household.</p>
    @else
        @foreach ($this->appliances as $appliance)
            <div wire:key="{{ $appliance->id }}">
                <div class="mt-8 flex items-center gap-4">
                    <h2 class="text-xl font-semibold">{{ $appliance->name }} ({{ $appliance->appliance_type }})</h2>

                    <button
                        type="button"
                        wire:click="removeAppliance(@js($appliance->id))"
                        wire:confirm="{{ 'Remove '.$appliance->name.' and all its shelves?' }}"
                        wire:loading.attr="disabled"
                        class="inline-flex cursor-pointer items-center rounded border border-red-200 bg-white px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-50 disabled:cursor-wait disabled:opacity-50"
                    >
                        Remove
                    </button>
                </div>

                @if ($status && $statusApplianceId === $appliance->id)
                    <p role="status" class="mt-2 text-sm">{{ $status }}</p>
                @endif

                <ul class="mt-2 flex max-w-xl flex-col divide-y divide-stone-200 border border-stone-200 bg-white">
                    @if ($appliance->shelves->isEmpty())
                        <li class="px-3 py-2 text-sm text-stone-500">This appliance has no shelves.</li>
                    @endif

                    {{-- Columns: name (flex-1), load (w-66, same as the capacity bar), action (w-12); the add-shelf row uses the same widths. --}}
                    @foreach ($appliance->shelves as $shelf)
                        <li wire:key="{{ $shelf->id }}" class="flex items-center gap-3 px-3 py-2">
                            <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ $shelf->label }}</span>

                            <x-shelf.capacity-bar
                                class="w-66 shrink-0"
                                :current-weight="$shelf->stored_weight ?? 0"
                                :max-weight="$shelf->max_load_capacity_g"
                            />

                            <div class="flex w-12 shrink-0 justify-end">
                                <button
                                    type="button"
                                    wire:click="removeShelf(@js($appliance->id), @js($shelf->id))"
                                    wire:loading.attr="disabled"
                                    aria-label="Remove shelf {{ $shelf->label }}"
                                    title="Remove shelf"
                                    class="flex size-6 cursor-pointer items-center justify-center rounded text-stone-500 hover:bg-red-50 hover:text-red-700 disabled:cursor-wait disabled:opacity-50"
                                >&times;</button>
                            </div>
                        </li>
                    @endforeach

                    <x-shelf.add-form :household="$household" :appliance="$appliance" />
                </ul>
            </div>
        @endforeach
    @endif
</div>
