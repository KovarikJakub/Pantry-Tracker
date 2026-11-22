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
<div class="mt-6 max-w-2xl">
    @if ($status && $statusApplianceId === null)
        <p role="status" class="mb-4 rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6]/60 px-4 py-2 text-sm text-[#2F4A3A]">
            {{ $status }}
        </p>
    @endif

    @if ($this->appliances->isEmpty())
            <p class="text-sm text-[#2F4A3A]/60">There are no appliances in this household.</p>
    @else
        <div class="space-y-8">
        @foreach ($this->appliances as $appliance)
            <section wire:key="{{ $appliance->id }}" class="space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="flex items-center text-xl font-semibold tracking-tight text-[#2F4A3A]">
                        {{ $appliance->name }}
                        <span class="ml-3 rounded-full bg-[#EFEBF6] px-3 py-0.5 text-xs font-medium text-[#2F4A3A]">
                                {{ $appliance->appliance_type }}
                        </span>
                    </h2>

                    <button
                        type="button"
                        wire:click="removeAppliance(@js($appliance->id))"
                        wire:confirm="{{ 'Remove '.$appliance->name.' and all its shelves?' }}"
                        wire:loading.attr="disabled"
                        class="inline-flex cursor-pointer items-center rounded-full border border-red-600/30 bg-transparent px-3 py-1 text-xs font-medium text-red-700 transition hover:border-red-600 hover:bg-red-50 disabled:cursor-wait disabled:opacity-50"
                    >
                        Remove
                    </button>
                </div>

                @if ($status && $statusApplianceId === $appliance->id)
                    <p role="status" class="rounded-xl border border-[#2F4A3A]/15 bg-red-200/30 px-4 py-2 text-sm text-[#2F4A3A]">
                        {{ $status }}
                    </p>
                @endif

                <ul class="flex flex-col divide-y divide-[#2F4A3A]/10 overflow-hidden rounded-xl border border-[#2F4A3A]/15 bg-white shadow-sm">
                    @if ($appliance->shelves->isEmpty())
                        <li class="px-4 py-3 text-sm italic text-[#2F4A3A]/50">This appliance has no shelves.</li>
                    @endif

                    {{-- Columns: name (flex-1), load (w-66, same as the capacity bar), action (w-12); the add-shelf row uses the same widths. --}}
                    @foreach ($appliance->shelves as $shelf)
                        <li wire:key="{{ $shelf->id }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-[#F4F6F4]/60">
                            <span class="min-w-0 flex-1 truncate text-sm font-medium text-[#2F4A3A]">
                                    {{ $shelf->label }}
                            </span>

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
                                    class="flex size-7 cursor-pointer items-center justify-center rounded-lg text-[#2F4A3A]/50 transition hover:bg-red-50 hover:text-red-700 disabled:cursor-wait disabled:opacity-50"
                                >&times;</button>
                            </div>
                        </li>
                    @endforeach

                    <x-shelf.add-form :household="$household" :appliance="$appliance" />
                </ul>
            </section>
        @endforeach
        </div>
    @endif
</div>
