<x-layouts::app>
    <div class="max-w-5xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold">My Consumption Profile</h1>

        {{-- Period Filter --}}
        <div class="flex flex-wrap items-end gap-4">
            <a href="{{ route('consumption.index', ['period' => 'day']) }}"
               class="px-3 py-1.5 rounded border {{ $period === 'day' ? 'bg-black text-white' : '' }}">
                Today
            </a>
            <a href="{{ route('consumption.index', ['period' => 'week']) }}"
               class="px-3 py-1.5 rounded border {{ $period === 'week' ? 'bg-black text-white' : '' }}">
                Last 7 Days
            </a>

            <form method="GET" action="{{ route('consumption.index') }}" class="flex items-end gap-2">
                <input type="hidden" name="period" value="custom">
                <div>
                    <label for="from" class="block text-xs">From</label>
                    <input id="from" type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1">
                </div>
                <div>
                    <label for="to" class="block text-xs">To</label>
                    <input id="to" type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1">
                </div>
                <button type="submit" class="px-3 py-1.5 rounded border bg-gray-100">Filter</button>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="p-4 border rounded">
                <div class="text-xs text-gray-500">Calories</div>
                <div class="text-xl font-semibold">{{ number_format($nutrition['calories'], 0) }} kcal</div>
            </div>
            <div class="p-4 border rounded">
                <div class="text-xs text-gray-500">Protein</div>
                <div class="text-xl font-semibold">{{ number_format($nutrition['protein'], 1) }} g</div>
            </div>
            <div class="p-4 border rounded">
                <div class="text-xs text-gray-500">Fat</div>
                <div class="text-xl font-semibold">{{ number_format($nutrition['fat'], 1) }} g</div>
            </div>
            <div class="p-4 border rounded">
                <div class="text-xs text-gray-500">Carbs</div>
                <div class="text-xl font-semibold">{{ number_format($nutrition['carbs'], 1) }} g</div>
            </div>
            <div class="p-4 border rounded">
                <div class="text-xs text-gray-500">Fiber</div>
                <div class="text-xl font-semibold">{{ number_format($nutrition['fiber'], 1) }} g</div>
            </div>
            <div class="p-4 border rounded">
                <div class="text-xs text-gray-500">Total Cost</div>
                <div class="text-xl font-semibold">{{ number_format($nutrition['price'], 2) }} Kč</div>
            </div>
        </div>

        {{-- Consumed Items Table --}}
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="border-b">
                <th class="py-2">Food</th>
                <th class="py-2">Consumed At</th>
                <th class="py-2">Amount</th>
                <th class="py-2">Calories</th>
                <th class="py-2">Price</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                @php
                    $itemCalories = ($item->product->calories_per_100g ?? 0) * (($item->weight_volume ?? 0) / 100);
                @endphp
                <tr class="border-b">
                    <td class="py-2">{{ $item->product->name }}</td>
                    <td class="py-2">{{ $item->removed_at?->format('d.m.Y H:i') }}</td>
                    <td class="py-2">{{ $item->weight_volume }} {{ $item->product->measurement_unit }}</td>
                    <td class="py-2">{{ number_format($itemCalories, 0) }} kcal</td>
                    <td class="py-2">{{ number_format($item->purchase_price ?? 0, 2) }} Kč</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-500">
                        No consumed food recorded in this period.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-layouts::app>
