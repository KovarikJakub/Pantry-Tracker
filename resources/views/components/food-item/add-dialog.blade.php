@props(['household', 'shelfLabels'])

{{--
    One shared modal for putting a catalogue product on a shelf. Each shelf header opens it by dispatching
    the window event "open-add-food" with { id, label }. Clicking "Add" on a product card submits the form.
--}}

@php
    $hasErrors = $errors->foodItem->any();
    $failedShelfId = $hasErrors ? old('shelf_id') : null;
@endphp

<div
    x-data="{
        shelfId: @js($failedShelfId),
        shelfLabel: @js($failedShelfId ? $shelfLabels->get($failedShelfId) : null),
        open(shelf) {
            this.shelfId = shelf.id;
            this.shelfLabel = shelf.label;
            this.$refs.form.reset();
            this.$refs.dialog.showModal();
        },
    }"
    x-init="@js($hasErrors) && $refs.dialog.showModal()"
    @open-add-food.window="open($event.detail)"
>
    <dialog
        x-ref="dialog"
        aria-labelledby="add-food-title"
        class="m-auto w-full max-w-3xl border bg-white p-0 text-sm shadow-xl backdrop:bg-black/40"
        @click.self="$refs.dialog.close()"
    >
        <form
            x-ref="form"
            id="add-food-item"
            method="POST"
            action="{{ route('households.food-items.store', $household->id) }}"
            class="space-y-4 p-6"
        >
            @csrf
            <input type="hidden" name="shelf_id" :value="shelfId">

            <div class="flex items-start justify-between gap-4">
                <h2 id="add-food-title" class="text-lg font-semibold">
                    Add food to <span x-text="shelfLabel"></span>
                </h2>
                <button type="button" class="underline" @click="$refs.dialog.close()">Close</button>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <label class="flex flex-col gap-1">
                    Price
                    <input name="purchase_price" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('purchase_price') }}" required autofocus class="border px-2 py-1">
                </label>
                <label class="flex flex-col gap-1">
                    Use by
                    <input name="expiration_date" type="date" value="{{ old('expiration_date') }}" required class="border px-2 py-1">
                </label>
                <span class="text-stone-500">Then click Add on a product.</span>
            </div>

            @foreach ($errors->foodItem->all() as $message)
                <p role="alert" class="text-red-700">{{ $message }}</p>
            @endforeach

            <x-product.catalog :products="$household->products" />
        </form>
    </dialog>
</div>
