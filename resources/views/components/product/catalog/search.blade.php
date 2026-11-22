{{-- Enter is swallowed: inside the "Add food" form it would otherwise submit with the first product's "Add" button. --}}
<div class="relative">
    <svg
        aria-hidden="true"
        class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-[#2F4A3A]/45"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <circle cx="11" cy="11" r="8" />
        <path d="m21 21-4.3-4.3" />
    </svg>

    <input
        type="search"
        x-model="search"
        @keydown.enter.prevent
        placeholder="Search all products…"
        aria-label="Search all products by name"
        class="w-full rounded-xl border border-[#2F4A3A]/20 bg-white py-2 pl-10 pr-4 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
    >
</div>
