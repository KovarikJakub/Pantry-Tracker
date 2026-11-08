@props(['household', 'members'])

{{-- Household members as cards: initials avatar, name, email, role badge and join date. Managers first. --}}

@php
    $currentUserId = request()->user()?->id;
    $sortedMembers = $members->sortBy([
        fn ($a, $b) => ($a->pivot->role === 'manager' ? 0 : 1) <=> ($b->pivot->role === 'manager' ? 0 : 1),
        fn ($a, $b) => strcasecmp($a->fullName(), $b->fullName()),
    ]);
@endphp

<section aria-labelledby="members-title" class="mt-6 max-w-2xl">
    <h2 id="members-title" class="mb-3 text-lg font-semibold">
        Members <span class="text-sm font-normal text-stone-500">({{ $members->count() }})</span>
    </h2>

    @error('member', 'memberRemove')
        <p role="alert" class="mb-2 text-red-600">{{ $message }}</p>
    @enderror

    <ul class="divide-y divide-stone-200 border border-stone-200 bg-white">
        @foreach ($sortedMembers as $member)
            @php($isManager = $member->pivot->role === 'manager')
            <li class="flex items-center gap-3 px-4 py-3">
                <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-stone-200 text-sm font-semibold text-stone-700">
                    {{ mb_strtoupper(mb_substr($member->first_name, 0, 1).mb_substr($member->last_name, 0, 1)) }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">
                        {{ $member->fullName() }}
                        @if ($member->id === $currentUserId)
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
                    <form method="POST" action="{{ route('households.members.destroy', [$household->id, $member->id]) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Remove</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>
</section>
