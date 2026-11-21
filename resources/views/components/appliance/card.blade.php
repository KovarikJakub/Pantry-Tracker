@props(['household', 'appliance', 'read-only' => false])

@if ($appliance->shelves->isEmpty())
    <p class="mt-2">This appliance has no shelves.</p>
@else
    <div class="mt-3 space-y-3">
        @foreach ($appliance->shelves as $shelf)
            <livewire:shelf.card :household="$household->withoutRelations()" :shelf="$shelf->withoutRelations()" :key="$shelf->id" :read-only="$readOnly" />
        @endforeach
    </div>
@endif
