<?php

use Illuminate\Support\Carbon;

it('describes how far away the expiry date is', function (int $daysFromNow, string $text, string $color) {
    $this->travelTo(Carbon::parse('2026-01-15 10:00'));

    $this->blade('<x-food-item.expiry-tag :expiry-date="$date" />', ['date' => today()->addDays($daysFromNow)])
        ->assertSee($text)
        ->assertSee($color);
})->with([
    'expired' => [-1, 'Expired', 'bg-red-100'],
    'today' => [0, 'Today', 'bg-red-100'],
    'one day' => [1, '1 day', 'bg-red-100'],
    'two days' => [2, '2 days', 'bg-red-100'],
    'three days' => [3, '3 days', 'bg-orange-100'],
    'one week' => [7, '7 days', 'bg-orange-100'],
    'two weeks' => [14, '14 days', 'bg-blue-100'],
    'three weeks' => [21, '21 days', 'bg-stone-100'],
    'just over three weeks' => [22, '> 3 weeks', 'bg-stone-100'],
    'just under two months' => [58, '> 8 weeks', 'bg-stone-100'],
    'two months' => [59, '> 2 months', 'bg-stone-100'],
    'two years' => [731, '> 2 years', 'bg-stone-100'],
]);
