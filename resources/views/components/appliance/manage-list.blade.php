<?php

use App\Models\Household;
use Livewire\Component;

new class extends Component
{
    public Household $household;

    public ?string $status = null;

    /**
     * Runs before every action. Route middleware does not protect Livewire's update requests.
     */
    public function hydrate(): void
    {
        $this->authorize('manage', $this->household);
    }

    /**
     * Removes an appliance with its shelves. Refused while food is still stored in it.
     */
    public function remove(string $applianceId): void
    {
        $appliance = $this->household->appliances()->findOrFail($applianceId);

        $hasStoredFood = $appliance->shelves()
            ->whereHas('foodItems', fn ($query) => $query->where('status', 'stored'))
            ->exists();

        if ($hasStoredFood) {
            $this->status = "{$appliance->name} still contains food. Remove the food first.";

            return;
        }

        $appliance->delete();

        $this->status = "{$appliance->name} was removed.";
        $this->household->unsetRelation('appliances');
    }
};
?>

{{-- Appliances with their shelves and the "Add shelf" form, for the Appliances tab. --}}
<div>
    @if ($status)
        <p role="status" class="mt-4">{{ $status }}</p>
    @endif

    @if ($household->appliances->isEmpty())
        <p class="mt-4">There are no appliances in this household.</p>
    @else
        @foreach ($household->appliances as $appliance)
            <div wire:key="{{ $appliance->id }}">
                <div class="mt-8 flex items-center gap-4">
                    <h2 class="text-xl font-semibold">{{ $appliance->name }} ({{ $appliance->appliance_type }})</h2>

                    <button
                        type="button"
                        wire:click="remove(@js($appliance->id))"
                        wire:confirm="{{ 'Remove '.$appliance->name.' and all its shelves?' }}"
                        wire:loading.attr="disabled"
                        class="inline-flex cursor-pointer items-center rounded border border-red-200 bg-white px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-50 disabled:cursor-wait disabled:opacity-50"
                    >
                        Remove
                    </button>
                </div>

                @if ($appliance->shelves->isEmpty())
                    <p class="mt-2">This appliance has no shelves.</p>
                @else
                    <ul class="mt-2 list-disc pl-6">
                        @foreach ($appliance->shelves as $shelf)
                            <li>{{ $shelf->label }} – max {{ (float) $shelf->max_load_capacity_g }} g</li>
                        @endforeach
                    </ul>
                @endif

                <x-shelf.add-form :household="$household" :appliance="$appliance" />
            </div>
        @endforeach
    @endif
</div>
