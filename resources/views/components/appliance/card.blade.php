@props(['household', 'appliance'])

@if ($appliance->shelves->isEmpty())
    <p class="mt-2">This appliance has no shelves.</p>
@else
    <div class="mt-3 space-y-3">
        @foreach ($appliance->shelves as $shelf)
            <livewire:shelf.card :household="$household->withoutRelations()" :shelf="$shelf->withoutRelations()" :key="$shelf->id" />
        @endforeach
    </div>
@endif
