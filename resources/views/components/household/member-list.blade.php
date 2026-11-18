<?php

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    private const ROLE_ORDER = [
        HouseholdMember::ROLE_OWNER => 0,
        HouseholdMember::ROLE_MANAGER => 1,
        HouseholdMember::ROLE_MEMBER => 2,
    ];

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
     * The household's users: owner, managers, members, each group by name.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function members(): Collection
    {
        return $this->household->memberUsers()->get()->sortBy([
            fn (User $a, User $b) => (self::ROLE_ORDER[$a->pivot->role] ?? 9) <=> (self::ROLE_ORDER[$b->pivot->role] ?? 9),
            fn (User $a, User $b) => strcasecmp($a->fullName(), $b->fullName()),
        ])->values();
    }

    /**
     * Memberships keyed by user id, used to decide which actions the current user may take on each row.
     *
     * @return Collection<string, HouseholdMember>
     */
    #[Computed]
    public function memberships(): Collection
    {
        return $this->household->members()->get()->keyBy('user_id');
    }

    /**
     * The current user's own membership.
     */
    #[Computed]
    public function actor(): HouseholdMember
    {
        return $this->memberships[request()->user()->id];
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
            'role' => HouseholdMember::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        $this->reset('email');
        $this->status = 'User was added.';
        $this->refreshMembers();
    }

    /**
     * Removes a user from the household; the user account itself stays.
     */
    public function remove(string $userId): void
    {
        $target = $this->membership($userId);

        if (! $this->actor->canRemove($target)) {
            $this->status = 'You cannot remove this user.';

            return;
        }

        $target->delete();

        $this->status = 'User was removed.';
        $this->refreshMembers();
    }

    /**
     * Makes a regular member a manager.
     */
    public function promote(string $userId): void
    {
        $target = $this->membership($userId);

        if (! $this->actor->canPromote($target)) {
            $this->status = 'You cannot promote this user.';

            return;
        }

        $target->update(['role' => HouseholdMember::ROLE_MANAGER]);

        $this->status = 'User is now a manager.';
        $this->refreshMembers();
    }

    /**
     * Makes a manager a regular member. Owner only.
     */
    public function demote(string $userId): void
    {
        $target = $this->membership($userId);

        if (! $this->actor->canDemote($target)) {
            $this->status = 'You cannot demote this user.';

            return;
        }

        $target->update(['role' => HouseholdMember::ROLE_MEMBER]);

        $this->status = 'User is now a member.';
        $this->refreshMembers();
    }

    private function membership(string $userId): HouseholdMember
    {
        return $this->household->members()->where('user_id', $userId)->firstOrFail();
    }

    private function refreshMembers(): void
    {
        unset($this->members, $this->memberships, $this->actor);
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

    {{-- Columns: avatar (size-10), name (flex-1), role (w-32), actions (w-48); the add-member row uses the same widths. --}}
    <ul class="divide-y divide-stone-200 border border-stone-200 bg-white">
        @foreach ($this->members as $member)
            @php
                $role = $member->pivot->role;
                $membership = $this->memberships[$member->id];
            @endphp
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

                <div class="w-32 shrink-0 text-right">
                    <span @class([
                        'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                        'bg-emerald-100 text-emerald-800' => $role === HouseholdMember::ROLE_OWNER,
                        'bg-amber-100 text-amber-800' => $role === HouseholdMember::ROLE_MANAGER,
                        'bg-stone-100 text-stone-700' => $role === HouseholdMember::ROLE_MEMBER,
                    ])>{{ ucfirst($role) }}</span>
                    <p class="mt-1 text-xs text-stone-500">
                        Joined {{ \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->toFormattedDateString() }}
                    </p>
                </div>

                @php($actionClass = 'inline-flex cursor-pointer items-center rounded border px-2 py-1 text-xs font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-wait disabled:opacity-50')
                <div class="flex w-48 shrink-0 justify-end gap-2" wire:loading.class="pointer-events-none" wire:target="promote, demote, remove">
                    @if ($this->actor->canPromote($membership))
                        <button
                            type="button"
                            wire:click="promote(@js($member->id))"
                            wire:loading.attr="disabled"
                            aria-label="Promote {{ $member->fullName() }} to manager"
                            title="Make manager"
                            class="{{ $actionClass }} border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 focus-visible:outline-amber-500"
                        >
                            Promote
                        </button>
                    @endif
                    @if ($this->actor->canDemote($membership))
                        <button
                            type="button"
                            wire:click="demote(@js($member->id))"
                            wire:confirm="{{ 'Demote '.$member->fullName().' to a regular member?' }}"
                            wire:loading.attr="disabled"
                            aria-label="Demote {{ $member->fullName() }} to member"
                            title="Make regular member"
                            class="{{ $actionClass }} border-stone-300 bg-white text-stone-700 hover:bg-stone-100 focus-visible:outline-stone-500"
                        >
                            Demote
                        </button>
                    @endif
                    @if ($this->actor->canRemove($membership))
                        <button
                            type="button"
                            wire:click="remove(@js($member->id))"
                            wire:confirm="{{ 'Remove '.$member->fullName().' from the household?' }}"
                            wire:loading.attr="disabled"
                            aria-label="Remove {{ $member->fullName() }} from the household"
                            title="Remove from household"
                            class="{{ $actionClass }} border-red-200 bg-white text-red-700 hover:bg-red-50 focus-visible:outline-red-500"
                        >
                            Remove
                        </button>
                    @endif
                </div>
            </li>
        @endforeach

        <x-household.add-member-form />
    </ul>
</section>
