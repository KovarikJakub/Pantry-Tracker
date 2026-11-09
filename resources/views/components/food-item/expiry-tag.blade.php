@props(['expiryDate'])

@php
    $expiry = \Illuminate\Support\Carbon::parse($expiryDate)->startOfDay();
    $today = today();
    $days = (int) $today->diffInDays($expiry, false);
    $months = (int) $today->diffInMonths($expiry);
    $years = (int) $today->diffInYears($expiry);

    $text = match (true) {
        $days < 0 => 'Expired',
        $days === 0 => 'Today',
        $days <= 21 => trans_choice(':count day|:count days', $days),
        $years >= 2 => "> {$years} years",
        $months >= 2 => "> {$months} months",
        default => '> '.intdiv($days, 7).' weeks',
    };

    $colors = match (true) {
        $days <= 2 => 'bg-red-100 text-red-800',
        $days <= 7 => 'bg-orange-100 text-orange-800',
        $days <= 14 => 'bg-blue-100 text-blue-800',
        default => 'bg-stone-100 text-stone-700',
    };
@endphp

<time
    datetime="{{ $expiry->toDateString() }}"
    {{ $attributes->merge(['class' => "group/expiry inline-flex w-24 justify-center rounded-full py-0.5 text-xs font-medium whitespace-nowrap tabular-nums {$colors}"]) }}
>
    <span class="group-hover/expiry:hidden">{{ $text }}</span>
    <span class="hidden group-hover/expiry:inline">{{ $expiry->toDateString() }}</span>
</time>
