@props(['household'])

{{--
    Holds which shelf quick-add puts products on. The search bar and the shelf cards inside the slot share this
    Alpine state. It is kept for the browser tab, because eating or removing food reloads the page.
--}}

@php
    /** @var \Illuminate\Support\Collection<string, string> $shelfLabels shelf id => "Appliance – Shelf" */
    $shelfLabels = $household->appliances->flatMap(
        fn ($appliance) => $appliance->shelves->mapWithKeys(fn ($shelf) => [$shelf->id => "{$appliance->name} – {$shelf->label}"]),
    );
@endphp

<div
    x-data="{
        activeShelfId: $persist(null).as(@js('active-shelf-'.$household->id)).using(sessionStorage),
        shelfLabels: @js($shelfLabels),
        needsShelfPrompt: false,
        get activeShelfLabel() {
            return this.shelfLabels[this.activeShelfId] ?? null;
        },
        selectShelf(shelfId) {
            this.activeShelfId = shelfId;
            this.needsShelfPrompt = false;
            this.$dispatch('shelf-selected');
        },
        toggleShelf(shelfId) {
            if (this.activeShelfId === shelfId) {
                this.activeShelfId = null;

                return;
            }

            this.selectShelf(shelfId);
        },
        clearShelfOnOutsideClick(target) {
            if (! target.closest('[data-shelf-card], [data-keeps-shelf-selection], dialog')) {
                this.activeShelfId = null;
                this.needsShelfPrompt = false;
            }
        },
    }"
    @click.window="clearShelfOnOutsideClick($event.target)"
>
    {{ $slot }}
</div>
