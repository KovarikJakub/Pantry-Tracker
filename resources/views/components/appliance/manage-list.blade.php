@props(['household'])

{{-- Appliances with their shelves and the "Add shelf" form, for the Appliances tab. --}}
@if ($household->appliances->isEmpty())
    <p class="mt-4">There are no appliances in this household.</p>
@else
    @foreach ($household->appliances as $appliance)
        <h2 class="mt-8 text-xl font-semibold">{{ $appliance->name }} ({{ $appliance->appliance_type }})</h2>

        @if ($appliance->shelves->isEmpty())
            <p class="mt-2">This appliance has no shelves.</p>
        @else
            <ul class="mt-2 list-disc pl-6">
                @foreach ($appliance->shelves as $shelf)
                    <li>{{ $shelf->label }} – max {{ (float) $shelf->max_load_capacity_g }} g</li>
                @endforeach
            </ul>
        @endif

        <x-shelf.add-form :household="$household" :appliance="$appliance" />
    @endforeach
@endif
