@php($readOnly ??= false)
@php($user = $currentUser ?? request()->user())

@if ($household)
    <x-household.layout :household="$household" :households="$households ?? collect()" :read-only="$readOnly">
        @if ($readOnly)
            <div
                role="status"
                class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6] px-4 py-2.5 text-sm text-[#2F4A3A]"
            >
                <div class="flex items-center gap-2.5">
                    <svg
                        aria-hidden="true"
                        class="size-4 shrink-0 text-[#6E5D8F]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <p>
                        You are viewing <span class="font-medium">{{ $household->name }}</span> in read-only mode.
                    </p>
                </div>

                @empty($currentUser)
                    <a
                        href="{{ route('register') }}"
                        class="shrink-0 rounded-full bg-[#2F4A3A] px-3.5 py-1 text-xs font-medium text-white transition hover:bg-[#2F4A3A]/90"
                    >
                        Register
                    </a>
                @endempty
            </div>
        @endif

    @if (session('food_item_status'))
                <p class="mt-4 rounded-lg border border-[#d78c7d]/50 bg-[#d78c7d]/15 px-4 py-2 text-sm text-[#2F4A3A]">
                    {{ session('food_item_status') }}</p>
    @endif
    <div class="mt-6 border-[#2F4A3A]/15 pb-5 text-[#2F4A3A]">
        <livewire:household.expiring-soon :household="$household->withoutRelations()" />
    </div>

    @unless ($readOnly)
            <x-food-item.add-dialog
                :household="$household"
                :shelf-labels="$household->appliances->flatMap->shelves->pluck('label', 'id')"
                :categories-by-parent="$catalogueCategoriesByParent"
                :products="$catalogueProducts"
                :product-category-ids="$productCategoryIds"
            />
            <x-food-item.resolve-dialog />
        @endunless

    <x-shelf.selection :household="$household">
        @unless ($readOnly)
            {{-- Hidden without shelves, as there is nowhere to add food. Sticky, so the bar stays reachable while picking a shelf further down the page. --}}
            @if ($household->appliances->contains(fn ($appliance) => $appliance->shelves->isNotEmpty()))
                <div class="sticky top-0 z-30 mt-2 bg-[#F4F6F4]/90 py-2 backdrop-blur-sm">
                    <h2 class="mb-2 text-base font-semibold text-[#2F4A3A]">Quick Add</h2>
                    <livewire:product.searchbar :household="$household->withoutRelations()" />
                </div>
            @endif
        @endunless

        <x-appliance.list :household="$household" :read-only="$readOnly"/>
    </x-shelf.selection>
    </x-household.layout>
@else
    <x-layouts::app>
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold tracking-tight text-[#2F4A3A]">
                Hi, {{$user?->first_name}}
            </h1>
            <x-household.create-popup />
        </header>

        <p>You don't have any households yet.</p>
    </x-layouts::app>
@endif
