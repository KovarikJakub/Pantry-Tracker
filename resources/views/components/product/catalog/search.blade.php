{{-- Enter is swallowed: inside the "Add food" form it would otherwise submit with the first product's "Add" button. --}}
<input
    type="search"
    x-model="search"
    @keydown.enter.prevent
    placeholder="Search all products…"
    aria-label="Search all products by name"
    class="w-full border px-2 py-1"
>
