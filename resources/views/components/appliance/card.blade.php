@props(['household', 'appliance', 'read-only' => false])

@if ($appliance->shelves->isEmpty())
    <p class="mt-2">This appliance has no shelves.</p>
@else
    <div class="mt-3 space-y-3">
        @foreach ($appliance->shelves as $shelf)
            <x-shelf.card :household="$household" :shelf="$shelf" :read-only="$readOnly" />
        @endforeach
    </div>
@endif
