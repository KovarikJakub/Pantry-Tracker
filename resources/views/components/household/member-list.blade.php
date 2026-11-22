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

<section aria-labelledby="members-title" class="mt-6 max-w-3xl">
    <h2 id="members-title" class="mb-3 text-lg font-semibold text-[#2F4A3A]">
        Members <span class="text-sm font-normal text-[#2F4A3A]/55">({{ $this->members->count() }})</span>
    </h2>

    @if ($status)
        <p role="status" class="mb-4 rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6]/60 px-4 py-2 text-sm text-[#2F4A3A]">
            {{ $status }}
        </p>
    @endif

    {{-- Columns: avatar (size-10), name (flex-1), role (w-32), actions (w-48); the add-member row uses the same widths. --}}
    <ul class="divide-y divide-[#2F4A3A]/10 overflow-hidden rounded-xl border border-[#2F4A3A]/15 bg-white shadow-sm">
        @foreach ($this->members as $member)
            @php
                $role = $member->pivot->role;
                $membership = $this->memberships[$member->id];
            @endphp
            <li wire:key="{{ $member->id }}" class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-[#F4F6F4]/60">
                <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#EFEBF6] text-sm font-semibold text-[#2F4A3A]">
                    {{ mb_strtoupper(mb_substr($member->first_name, 0, 1).mb_substr($member->last_name, 0, 1)) }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-[#2F4A3A]">
                        {{ $member->fullName() }}
                        @if ($member->id === request()->user()?->id)
                            <span class="text-sm font-normal text-[#2F4A3A]/55">(you)</span>
                        @endif
                    </p>
                    <p class="truncate text-sm text-[#2F4A3A]/60">{{ $member->email }}</p>
                </div>

                <div class="w-32 shrink-0 text-right">
                    <span @class([
                        'inline-block rounded-full px-2.5 py-0.5 text-xs font-medium',
                        'bg-[#2F4A3A] text-white' => $role === HouseholdMember::ROLE_OWNER,
                        'bg-[#EFEBF6] text-[#6E5D8F]' => $role === HouseholdMember::ROLE_MANAGER,
                        'bg-[#F4F6F4] text-[#2F4A3A]/75' => $role === HouseholdMember::ROLE_MEMBER,
                    ])>{{ ucfirst($role) }}</span>
                    <p class="mt-1 text-xs text-[#2F4A3A]/50">
                        Joined {{ \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->toFormattedDateString() }}
                    </p>
                </div>

                @php($actionClass = 'inline-flex cursor-pointer items-center rounded-full border px-3 py-1 text-xs font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-wait disabled:opacity-50')
                <div class="flex w-48 shrink-0 justify-end gap-2" wire:loading.class="pointer-events-none" wire:target="promote, demote, remove">
                    @if ($this->actor->canPromote($membership))
                        <button
                            type="button"
                            wire:click="promote(@js($member->id))"
                            wire:loading.attr="disabled"
                            aria-label="Promote {{ $member->fullName() }} to manager"
                            title="Make manager"
                            class="{{ $actionClass }} border-[#6E5D8F]/30 bg-[#EFEBF6]/60 text-[#6E5D8F] hover:border-[#6E5D8F] hover:bg-[#EFEBF6] focus-visible:outline-[#6E5D8F]"
                        >
                            Promote
                        </button>
                    @endif
                    {{-- REMOVED the extra </div> that was here --}}
                    @if ($this->actor->canDemote($membership))
                        <button
                            type="button"
                            wire:click="demote(@js($member->id))"
                            wire:confirm="{{ 'Demote '.$member->fullName().' to a regular member?' }}"
                            wire:loading.attr="disabled"
                            aria-label="Demote {{ $member->fullName() }} to member"
                            title="Make regular member"
                            class="{{ $actionClass }} border-[#2F4A3A]/25 bg-transparent text-[#2F4A3A] hover:border-[#2F4A3A] hover:bg-[#F4F6F4] focus-visible:outline-[#2F4A3A]"
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
                            class="{{ $actionClass }} border-red-600/30 bg-transparent text-red-700 hover:border-red-600 hover:bg-red-50 focus-visible:outline-red-600"
                        >
                            Remove
                        </button>
                    @endif
                </div>
            </li>
        @endforeach

        <x-household.add-member-form />
    </ul>
    @can('manage', $household)
        <div class="mt-8 pt-5 border-t border-[#2F4A3A]/10 pt-6">
            <h3 class="mb-3 text-sm font-semibold text-[#2F4A3A]">Share link</h3>

            @if ($household->share_token)
                @php($shareUrl = route('households.share-token', $household->share_token))

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Share link pill/box with Copy + Regenerate icons --}}
                    <div
                        x-data="{
                            copied: false,
                            copy() {
                                navigator.clipboard.writeText(@js($shareUrl));
                                this.copied = true;
                                setTimeout(() => this.copied = false, 2000);
                            }
                        }"
                        class="flex max-w-full items-center gap-2 rounded-xl border border-[#2F4A3A]/15 bg-white py-1.5 pl-3.5 pr-1.5 shadow-sm"
                    >
                                <span class="select-all truncate text-sm text-[#2F4A3A]">
                                    {{ $shareUrl }}
                                </span>

                        <div class="flex shrink-0 items-center gap-1 border-l border-[#2F4A3A]/15 pl-1.5">
                            {{-- Copy icon button --}}
                            <button
                                type="button"
                                @click="copy()"
                                aria-label="Copy share link"
                                class="group/tip relative inline-flex size-8 cursor-pointer items-center justify-center rounded-lg text-[#2F4A3A]/70 transition hover:bg-[#EFEBF6] hover:text-[#2F4A3A]"
                            >
                                <svg x-show="!copied" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2" />
                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2" />
                                </svg>
                                <svg x-show="copied" x-cloak class="size-4 text-[#2F4A3A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                <span
                                    x-text="copied ? 'Copied!' : 'Copy'"
                                    class="invisible absolute bottom-full left-1/2 z-10 mb-1.5 -translate-x-1/2 rounded bg-stone-800 px-2 py-0.5 text-xs whitespace-nowrap text-white group-hover/tip:visible group-focus-visible/tip:visible"
                                ></span>
                            </button>

                            {{-- Regenerate icon button --}}
                            <form method="POST" action="{{ route('households.share-token.store', $household->id) }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    aria-label="Regenerate"
                                    class="group/tip relative inline-flex size-8 cursor-pointer items-center justify-center rounded-lg text-[#2F4A3A]/70 transition hover:bg-[#EFEBF6] hover:text-[#2F4A3A]"
                                >
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                        <path d="M3 3v5h5" />
                                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                                        <path d="M16 16h5v5" />
                                    </svg>
                                    <span class="invisible absolute bottom-full left-1/2 z-10 mb-1.5 -translate-x-1/2 rounded bg-[#2F4A3A] px-2 py-0.5 text-xs whitespace-nowrap text-white group-hover/tip:visible group-focus-visible/tip:visible">
                                        Regenerate
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Disable Sharing --}}
                    <form method="POST" action="{{ route('households.share-token.destroy', $household->id) }}">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="cursor-pointer rounded-xl border border-red-600/35 bg-transparent px-3.5 py-2 text-sm font-medium text-red-700 transition hover:border-red-600 hover:bg-red-50"
                        >
                            Disable sharing
                        </button>
                    </form>
                </div>
            @else
                {{-- Shown when no share link exists --}}
                <form method="POST" action="{{ route('households.share-token.store', $household->id) }}">
                    @csrf
                    <button
                        type="submit"
                        class="cursor-pointer rounded-xl border border-[#2F4A3A]/30 bg-transparent px-4 py-2 text-sm font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/70"
                    >
                        Generate share link
                    </button>
                </form>
            @endif
        </div>
    @endcan
</section>
