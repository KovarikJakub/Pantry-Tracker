@props(['household', 'households' => null, 'title' => null, 'readOnly' => false])
@php($user = $currentUser ?? request()->user())
@php($households ??= $user ? \App\Models\Household::query()->whereHas('memberUsers', fn ($q) => $q->whereKey($user->id))->orderBy('name')->get() : collect())

{{-- Page frame shared by all household tabs: name, tab bar, content. --}}
<x-layouts::app :title="$title ? $household->name.' – '.$title : $household->name">

    @unless($readOnly)
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold tracking-tight text-[#2F4A3A]">
                Hi, {{$user?->first_name}}
            </h1>
            <a href="{{ route('households.store') }}"
               class="rounded-full border border-[#2F4A3A]/30 bg-transparent px-4 py-1.5 text-sm font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/70"
            >Create new household</a>
        </header>
            @if (($households ?? collect())->isNotEmpty())
                <div
                    x-data="{
                        canScrollLeft: false,
                        canScrollRight: false,
                        updateArrows() {
                            const el = this.$refs.scroller;
                            this.canScrollLeft = el.scrollLeft > 2;
                            this.canScrollRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 2;
                        },
                        scrollBy(px) {
                            this.$refs.scroller.scrollBy({ left: px, behavior: 'smooth' });
                        },
                    }"
                    x-init="$nextTick(() => updateArrows())"
                    @resize.window="updateArrows()"
                    class="flex items-center gap-2"
                >
                    {{-- Left arrow --}}
                    <button
                        type="button"
                        x-show="canScrollLeft"
                        @click="scrollBy(-200)"
                        aria-label="Scroll households left"
                        class="flex size-8 shrink-0 items-center justify-center rounded-full border border-[#2F4A3A]/20 bg-[#EFEBF6] text-[#2F4A3A] transition hover:border-[#d78c7d] hover:bg-[#d78c7d] hover:text-white"
                    >&larr;</button>

                    <nav x-ref="scroller"
                         @scroll="updateArrows()"
                         class="flex flex-1 gap-2 overflow-x-auto whitespace-nowrap py-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    >
                        @foreach ($households as $item)
                            @php($isActive = $household->id === $item->id)
                            <a
                                href="{{ route('households.show', $item->id) }}"
                                @if ($isActive) aria-current="page" @endif
                                class="shrink-0 rounded-full border px-4 py-1.5 text-sm font-medium transition {{ $isActive
                                    ? 'border-[#2F4A3A] bg-[#2F4A3A] text-white'
                                    : 'rounded-full border border-[#2F4A3A]/30 bg-transparent px-4 py-1.5 text-sm font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/70'
                                }}"
                            >
                                {{ $item->name }}
                            </a>
                        @endforeach
                    </nav>

                    {{-- Right arrow --}}
                    <button
                        type="button"
                        x-show="canScrollRight"
                        @click="scrollBy(200)"
                        aria-label="Scroll households right"
                        class="flex size-8 shrink-0 items-center justify-center rounded-full border border-[#2F4A3A]/20 bg-[#EFEBF6] text-[#2F4A3A] transition hover:border-[#d78c7d] hover:bg-[#d78c7d] hover:text-white"
                    >&rarr;</button>
                </div>
            @endif
        <x-household.tabs :household="$household" />
    @endunless

    {{ $slot }}
</x-layouts::app>
