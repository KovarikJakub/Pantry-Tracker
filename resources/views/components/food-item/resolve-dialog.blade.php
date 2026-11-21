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
            this.$refs.dialog.showModal();
            this.$nextTick(() => this.$refs.amountInput.select());
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
        class="m-auto w-full max-w-sm border bg-white p-0 text-sm text-stone-800 shadow-xl backdrop:bg-black/40"
        @click.self="$refs.dialog.close()"
    >
        <form method="POST" :action="action" class="space-y-4 p-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" :value="status">

            <h2 id="resolve-food-title" class="text-lg font-semibold">
                <span x-text="status === 'consumed' ? 'Eat' : 'Throw out'"></span>
                <span x-text="name"></span>
            </h2>

            <label class="flex flex-col gap-1">
                <span>Amount (<span x-text="unit"></span>) (max <span x-text="maxWeight"></span> <span x-text="unit"></span>)</span>
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
                    class="border px-2 py-1"
                >
            </label>

            <div class="flex gap-2 text-xs">
                <button type="button" class="border px-2 py-1" @click="setFraction(0.25)">25%</button>
                <button type="button" class="border px-2 py-1" @click="setFraction(0.5)">50%</button>
                <button type="button" class="border px-2 py-1" @click="setFraction(1)">All</button>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="underline" @click="$refs.dialog.close()">Cancel</button>
                <button type="submit" class="bg-stone-800 px-3 py-1 text-white" x-text="status === 'consumed' ? 'Eat' : 'Throw out'"></button>
            </div>
        </form>
    </dialog>
</div>
