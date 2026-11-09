<?php

use App\Models\Household;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Database\Eloquent\Builder;


new class extends Component {

    #[Validate('required|min:5|max:255')]
    public string $name = '';

    /**
     * The households ordered by name.
     *
     * @return Collection<int, Household>
     */
    #[Computed]
    public function households(): Collection
    {
        $userId = request()->user()?->id;
        $households = Household::query()
            ->whereHas('members', fn (Builder $members) => $members->where('user_id', $userId))
            ->orderBy('name')
            ->get();

        return $households;
    }

    /**
     * Adds a new household
     */
    public function add(): void
    {
        $userId = request()->user()?->id;

        $validated = $this->validate();

        $householdName =  $validated['name'];

        DB::transaction(function () use ($householdName, $userId): void {
            $household = Household::query()->create(['name' => $householdName]);

            $household->members()->create([
                'user_id' => $userId,
                'role' => 'manager',
                'joined_at' => now(),
            ]);
        });

        unset($this->households);
        reset($this->name);
    }
};
?>

<div>
    @if ($this->households->isEmpty())
        <p class="mt-4">You are not a member of any household yet.</p>
    @else
        <ul class="mt-4 list-disc pl-6">
            @foreach ($this->households as $household)
                <li wire:key="{{ $household->id }}"><a href="{{ route('households.show', $household->id) }}">{{ $household->name }}</a></li>
            @endforeach
        </ul>
    @endif

    <form wire:submit="add" class="mt-8 flex max-w-sm flex-col gap-3">
        @csrf

        <label for="name">New household name</label>
        <input id="name" wire:model="name" type="text" value="{{ old('name') }}" required class="border p-2">

        @error('name')
            <p class="text-red-700">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-stone-800 p-2 text-white">Create household</button>
    </form>
</div>
