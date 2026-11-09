<?php

use App\Models\Household;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Household $household;

    public string $email = '';

    public ?string $status = null;

    /**
     * Runs before every action. Route middleware does not protect Livewire's update requests.
     */
    public function hydrate(): void
    {
        $this->authorize('manage', $this->household);
    }

    /**
     * The household's users, managers first, then by name.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        return $this->household->memberUsers()->get()->sortBy([
            fn (User $a, User $b) => ($a->pivot->role === 'manager' ? 0 : 1) <=> ($b->pivot->role === 'manager' ? 0 : 1),
            fn (User $a, User $b) => strcasecmp($a->fullName(), $b->fullName()),
        ])->values();
    }

    /**
     * Adds an existing user, found by email, to the household.
     */
    public function add(): void
    {
        $user = User::query()->where('email', $this->email)->first();

        if ($user === null) {
            $this->status = 'User does not exist.';

            return;
        }

        if ($this->household->members()->where('user_id', $user->id)->exists()) {
            $this->status = 'User is already in the household.';

            return;
        }

        $this->household->members()->create([
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $this->reset('email');
        $this->status = 'User was added.';
        unset($this->members);
    }

    /**
     * Removes a user from the household; the user account itself stays. Managers cannot be removed,
     * so a household always keeps at least one.
     */
    public function remove(string $userId): void
    {
        $membership = $this->household->members()->where('user_id', $userId)->firstOrFail();

        if ($membership->role === 'manager') {
            $this->status = 'A manager cannot be removed from the household.';

            return;
        }

        $membership->delete();

        $this->status = 'User was removed.';
        unset($this->members);
    }
};
?>

<section aria-labelledby="members-title" class="mt-6 max-w-2xl">
    <h2 id="members-title" class="mb-3 text-lg font-semibold">
        Members <span class="text-sm font-normal text-stone-500">({{ $this->members->count() }})</span>
    </h2>

    @if ($status)
        <p role="status" class="mb-2">{{ $status }}</p>
    @endif

    <ul class="divide-y divide-stone-200 border border-stone-200 bg-white">
        @foreach ($this->members as $member)
            @php($isManager = $member->pivot->role === 'manager')
            <li wire:key="{{ $member->id }}" class="flex items-center gap-3 px-4 py-3">
                <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-stone-200 text-sm font-semibold text-stone-700">
                    {{ mb_strtoupper(mb_substr($member->first_name, 0, 1).mb_substr($member->last_name, 0, 1)) }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">
                        {{ $member->fullName() }}
                        @if ($member->id === request()->user()?->id)
                            <span class="text-sm font-normal text-stone-500">(you)</span>
                        @endif
                    </p>
                    <p class="truncate text-sm text-stone-500">{{ $member->email }}</p>
                </div>

                <div class="shrink-0 text-right">
                    <span @class([
                        'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                        'bg-amber-100 text-amber-800' => $isManager,
                        'bg-stone-100 text-stone-700' => ! $isManager,
                    ])>{{ ucfirst($member->pivot->role) }}</span>
                    <p class="mt-1 text-xs text-stone-500">
                        Joined {{ \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->toFormattedDateString() }}
                    </p>
                </div>

                <div @class(['invisible' => $isManager])>
                    <button type="button" wire:click="remove(@js($member->id))">Remove</button>
                </div>
            </li>
        @endforeach
    </ul>

    <x-household.add-member-form />
</section>
