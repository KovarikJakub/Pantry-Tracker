@props(['household'])

{{-- Stored food that is expired or expires within a week, soonest first. Hidden when there is none. --}}

@php
    $maxShown = 5;
    $expiringItems = $household->appliances
        ->flatMap->shelves
        ->flatMap(fn ($shelf) => $shelf->foodItems->map(fn ($item) => ['item' => $item, 'shelfLabel' => $shelf->label]))
        ->filter(fn ($entry) => $entry['item']->expiration_date->lte(today()->addDays(7)))
        ->sortBy(fn ($entry) => $entry['item']->expiration_date)
        ->values();
@endphp

@if ($expiringItems->isNotEmpty())
    <section aria-labelledby="expiring-soon-title" class="mt-6 border border-orange-200 bg-orange-50 px-4 py-3 text-sm">
        <h2 id="expiring-soon-title" class="font-semibold">
            Expiring soon <span class="font-normal text-stone-600">({{ $expiringItems->count() }})</span>
        </h2>

        <ul class="mt-2 space-y-1">
            @foreach ($expiringItems->take($maxShown) as $entry)
                <li class="flex items-center gap-3">
                    <x-food-item.expiry-tag :expiry-date="$entry['item']->expiration_date" />
                    <span>{{ $entry['item']->product->name }}</span>
                    <span class="text-stone-500">{{ $entry['shelfLabel'] }}</span>
                </li>
            @endforeach
        </ul>

        @if ($expiringItems->count() > $maxShown)
            <p class="mt-2 text-stone-600">and {{ $expiringItems->count() - $maxShown }} more</p>
        @endif
    </section>
@endif
