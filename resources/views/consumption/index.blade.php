<x-layouts::app>
    <div class="mx-auto max-w-5xl space-y-6">
        <h1 class="text-2xl font-semibold tracking-tight text-[#2F4A3A]">My Consumption Profile</h1>

        {{-- Period Filter --}}
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-[#2F4A3A]/15 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('consumption.index', ['period' => 'day']) }}"
                    @class([
                        'rounded-full px-4 py-1.5 text-sm font-medium transition',
                        'bg-[#2F4A3A] text-white' => $period === 'day',
                        'border border-[#2F4A3A]/25 bg-white text-[#2F4A3A] hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/60' => $period !== 'day',
                    ])
                >
                    Today
                </a>

                <a
                    href="{{ route('consumption.index', ['period' => 'week']) }}"
                    @class([
                        'rounded-full px-4 py-1.5 text-sm font-medium transition',
                        'bg-[#2F4A3A] text-white' => $period === 'week',
                        'border border-[#2F4A3A]/25 bg-white text-[#2F4A3A] hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/60' => $period !== 'week',
                    ])
                >
                    Last 7 Days
                </a>
            </div>

            <form method="GET" action="{{ route('consumption.index') }}" class="flex flex-wrap items-end gap-2.5">
                <input type="hidden" name="period" value="custom">

                <div>
                    <label for="from" class="block text-xs font-medium text-[#2F4A3A]/75">From</label>
                    <input
                        id="from"
                        type="date"
                        name="from"
                        value="{{ $from }}"
                        class="mt-1 rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
                    >
                </div>

                <div>
                    <label for="to" class="block text-xs font-medium text-[#2F4A3A]/75">To</label>
                    <input
                        id="to"
                        type="date"
                        name="to"
                        value="{{ $to }}"
                        class="mt-1 rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
                    >
                </div>

                <button
                    type="submit"
                    class="cursor-pointer rounded-full bg-[#2F4A3A] px-4 py-1.5 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
                >
                    Filter
                </button>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-[#2F4A3A]/15 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium text-[#2F4A3A]/60">Calories</div>
                <div class="mt-1 text-xl font-semibold tabular-nums text-[#2F4A3A]">
                    {{ number_format($nutrition['calories'], 0, '.', '') }} <span class="text-sm font-normal text-[#2F4A3A]/60">kcal</span>
                </div>
            </div>

            <div class="rounded-xl border border-[#2F4A3A]/15 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium text-[#2F4A3A]/60">Protein</div>
                <div class="mt-1 text-xl font-semibold tabular-nums text-[#2F4A3A]">
                    {{ number_format($nutrition['protein'], 1) }} <span class="text-sm font-normal text-[#2F4A3A]/60">g</span>
                </div>
            </div>

            <div class="rounded-xl border border-[#2F4A3A]/15 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium text-[#2F4A3A]/60">Fat</div>
                <div class="mt-1 text-xl font-semibold tabular-nums text-[#2F4A3A]">
                    {{ number_format($nutrition['fat'], 1) }} <span class="text-sm font-normal text-[#2F4A3A]/60">g</span>
                </div>
            </div>

            <div class="rounded-xl border border-[#2F4A3A]/15 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium text-[#2F4A3A]/60">Carbs</div>
                <div class="mt-1 text-xl font-semibold tabular-nums text-[#2F4A3A]">
                    {{ number_format($nutrition['carbs'], 1) }} <span class="text-sm font-normal text-[#2F4A3A]/60">g</span>
                </div>
            </div>

            <div class="rounded-xl border border-[#2F4A3A]/15 bg-white p-4 shadow-sm">
                <div class="text-xs font-medium text-[#2F4A3A]/60">Fiber</div>
                <div class="mt-1 text-xl font-semibold tabular-nums text-[#2F4A3A]">
                    {{ number_format($nutrition['fiber'], 1) }} <span class="text-sm font-normal text-[#2F4A3A]/60">g</span>
                </div>
            </div>

            <div class="rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6]/60 p-4 shadow-sm">
                <div class="text-xs font-medium text-[#6E5D8F]">Total Cost</div>
                <div class="mt-1 text-xl font-semibold tabular-nums text-[#2F4A3A]">
                    {{ number_format($nutrition['price'], 2) }} <span class="text-sm font-normal text-[#2F4A3A]/70">Kč</span>
                </div>
            </div>
        </div>

        {{-- Consumed Items Table --}}
        <div class="overflow-hidden rounded-2xl border border-[#2F4A3A]/15 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm text-[#2F4A3A]">
                    <thead>
                    <tr class="border-b border-[#2F4A3A]/10 bg-[#F4F6F4]/70 text-xs font-semibold uppercase tracking-wide text-[#2F4A3A]/65">
                        <th class="px-4 py-3">Food</th>
                        <th class="px-4 py-3">Consumed At</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Calories</th>
                        <th class="px-4 py-3 text-right">Price</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2F4A3A]/10">
                    @forelse ($items as $item)
                        @php
                            $itemCalories = ($item->product->calories_per_100g ?? 0) * (($item->weight_volume ?? 0) / 100);
                        @endphp
                        <tr class="transition hover:bg-[#F4F6F4]/60">
                            <td class="px-4 py-3 font-medium text-[#2F4A3A]">{{ $item->product->name }}</td>
                            <td class="px-4 py-3 tabular-nums text-[#2F4A3A]/65">{{ $item->removed_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[#2F4A3A]/80">{{ (float) $item->weight_volume }} {{ $item->product->measurement_unit }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[#2F4A3A]/80">{{ number_format($itemCalories, 0) }} kcal</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums text-[#2F4A3A]">{{ number_format($item->purchase_price ?? 0, 2) }} Kč</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-[#2F4A3A]/55">
                                No consumed food recorded in this period.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
