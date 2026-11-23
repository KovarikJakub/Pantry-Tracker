@php
    $inputClass = 'w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15';
    $fractionButtonClass = 'cursor-pointer rounded-full border border-[#2F4A3A]/20 bg-[#F4F6F4] px-3 py-1 text-xs font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A]/40 hover:bg-[#EFEBF6]';
@endphp

<div
    x-data="{
        action: '',
        name: '',
        unit: 'g',
        status: 'consumed',
        maxWeight: 0,
        amount: '',
        open(item) {
            this.action = item.url;
            this.name = item.name;
            this.unit = item.unit;
            this.status = item.status;
            this.maxWeight = item.maxWeight;
            this.amount = item.maxWeight;
            document.body.classList.add('overflow-hidden');
            this.$refs.dialog.showModal();
            this.$nextTick(() => this.$refs.amountInput.select());
        },
        unlockScroll() {
            document.body.classList.remove('overflow-hidden');
        },
        setFraction(fraction) {
            this.amount = Math.round(this.maxWeight * fraction * 100) / 100;
        },
    }"
    @open-resolve-food.window="open($event.detail)"
>
    <dialog
        x-ref="dialog"
        aria-labelledby="resolve-food-title"
        @click.self="$refs.dialog.close()"
        @close="unlockScroll()"
        class="m-auto max-h-[90vh] w-[min(28rem,calc(100vw-2rem))] overflow-y-auto rounded-2xl border border-[#2F4A3A]/15 bg-white p-0 text-sm text-[#2F4A3A] shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-xs"
    >
        <form method="POST" :action="action" class="space-y-5 p-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" :value="status">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-4 border-b border-[#2F4A3A]/10 pb-4">
                <h2 id="resolve-food-title" class="flex flex-wrap items-center gap-2 text-lg font-semibold text-[#2F4A3A]">
                    <span x-text="status === 'consumed' ? 'Eat' : 'Throw out'"></span>
                    <span x-text="name" class="rounded-full bg-[#EFEBF6] px-3 py-0.5 text-sm font-medium text-[#2F4A3A]"></span>
                </h2>

                <button
                    type="button"
                    @click="$refs.dialog.close()"
                    aria-label="Close"
                    class="flex size-8 cursor-pointer items-center justify-center rounded-full text-lg text-[#2F4A3A]/60 transition hover:bg-[#F4F6F4] hover:text-[#2F4A3A]"
                >
                    &times;
                </button>
            </div>

            {{-- Amount & quick fractions --}}
            <div class="space-y-3">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">
                        Amount (<span x-text="unit"></span>)
                        <span class="font-normal text-[#2F4A3A]/55">(max <span x-text="maxWeight"></span> <span x-text="unit"></span>)</span>
                    </span>
                    <input
                        x-ref="amountInput"
                        name="amount"
                        type="number"
                        min="0.01"
                        :max="maxWeight"
                        step="0.01"
                        inputmode="decimal"
                        x-model="amount"
                        required
                        class="{{ $inputClass }}"
                    >
                </label>

                <div class="flex flex-wrap gap-2">
                    <button type="button" class="{{ $fractionButtonClass }}" @click="setFraction(0.25)">25 %</button>
                    <button type="button" class="{{ $fractionButtonClass }}" @click="setFraction(0.5)">50 %</button>
                    <button type="button" class="{{ $fractionButtonClass }}" @click="setFraction(1)">All</button>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center justify-end gap-2 border-t border-[#2F4A3A]/10 pt-4">
                <button
                    type="button"
                    @click="$refs.dialog.close()"
                    class="cursor-pointer rounded-full px-4 py-2 text-sm font-medium text-[#2F4A3A]/70 transition hover:bg-[#F4F6F4] hover:text-[#2F4A3A]"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :class="status === 'consumed' ? 'bg-[#2F4A3A] hover:bg-[#2F4A3A]/90' : 'bg-red-700 hover:bg-red-800'"
                    class="cursor-pointer rounded-full px-5 py-2 text-sm font-medium text-white transition"
                    x-text="status === 'consumed' ? 'Eat' : 'Throw out'"
                ></button>
            </div>
        </form>
    </dialog>
</div>
