@props(['appliance'])

@if ($appliance->shelves->isEmpty())
    <p class="mt-2">This appliance has no shelves.</p>
@else
    <table class="mt-2 border">
        <thead>
            <tr>
                <th class="border p-2 text-left">#</th>
                <th class="border p-2 text-left">Label</th>
                <th class="border p-2 text-left">Max load (g)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appliance->shelves as $shelf)
                <tr>
                    <td class="border p-2">{{ $shelf->shelf_order }}</td>
                    <td class="border p-2">{{ $shelf->label }}</td>
                    <td class="border p-2">{{ number_format((float) $shelf->max_load_capacity_g, 0, '.', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
