@props(['currentWeight', 'maxWeight'])

@php
    $current = (float) $currentWeight;
    $max = (float) $maxWeight;
    $percent = $max > 0 ? min(100, round($current / $max * 100)) : 100;
    $fillColor = match (true) {
        $percent >= 90 => 'bg-red-600',
        $percent >= 70 => 'bg-amber-500',
        default => 'bg-green-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 text-xs font-normal']) }}>
    <div
        role="progressbar"
        aria-label="Shelf load"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
        aria-valuenow="{{ $current }}"
        class="h-2 w-32 overflow-hidden rounded-full bg-stone-200"
    >
        <div class="h-full {{ $fillColor }}" style="width: {{ $percent }}%"></div>
    </div>
    <span class="tabular-nums"><b>{{ $current }} g</b> / {{ $max }} g</span>
</div>
