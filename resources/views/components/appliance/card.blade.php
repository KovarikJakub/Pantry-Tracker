@props(['household', 'appliance'])

@if ($appliance->shelves->isEmpty())
    <p class="mt-2">This appliance has no shelves.</p>
@else
    @foreach ($appliance->shelves as $shelf)
        <x-shelf.card :household="$household" :shelf="$shelf" />
    @endforeach
@endif
