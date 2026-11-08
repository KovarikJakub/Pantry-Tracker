@props(['household'])

@if ($household->appliances->isEmpty())
    <p class="mt-4">There are no appliances in this household.</p>
@else
    @foreach ($household->appliances as $appliance)
        <h2 class="mt-8 text-xl font-semibold">{{ $appliance->name }} ({{ $appliance->appliance_type }})</h2>
        <x-appliance.card :household="$household" :appliance="$appliance"/>

        @can('manage', $household)
            <x-shelf.add-form :household="$household" :appliance="$appliance" />
        @endcan
    @endforeach
@endif
