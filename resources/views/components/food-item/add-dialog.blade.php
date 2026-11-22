@props(['household', 'shelfLabels', 'categoriesByParent', 'products', 'productCategoryIds'])

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
    x-init="if (@js($hasErrors)) { document.body.classList.add('overflow-hidden'); $refs.dialog.showModal(); }"
    @open-add-food.window="open($event.detail)"
>
    <dialog
        x-ref="dialog"
        aria-labelledby="add-food-title"
        @click.self="$refs.dialog.close()"
        @close="unlockScroll()"
        class="m-auto h-[85vh] max-h-none w-[min(64rem,calc(100vw-2rem))] max-w-none overflow-hidden rounded-2xl border border-[#2F4A3A]/15 bg-white p-0 text-sm text-[#2F4A3A] shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-xs"
    >
        <form
            x-ref="form"
            id="add-food-item"
            method="POST"
            action="{{ route('households.food-items.store', $household->id) }}"
            class="flex h-full flex-col gap-4 p-6"
        >
            @csrf
            <input type="hidden" name="shelf_id" :value="shelfId">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-4 border-b border-[#2F4A3A]/10 pb-4">
                <h2 id="add-food-title" class="flex flex-wrap items-center gap-2 text-lg font-semibold text-[#2F4A3A]">
                    <span>Add food to</span>
                    <span x-text="shelfLabel"
                          class="rounded-full bg-[#EFEBF6] px-3 py-0.5 text-sm font-medium text-[#2F4A3A]"
                    ></span>
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

            {{-- Price & Use-by inputs --}}
            <div class="flex flex-wrap items-end gap-4 rounded-xl bg-[#F4F6F4] p-3.5">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">
                        Price <span class="font-normal text-[#2F4A3A]/55">(empty = default)</span>
                    </span>
                    <input name="purchase_price" type="number" min="0" step="1" inputmode="decimal" value="{{ old('purchase_price') }}" autofocus
                           class="w-44 rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
                    >
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Use by</span>
                    <input name="expiration_date" type="date" value="{{ old('expiration_date') }}" required
                           class="rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
                    >
                </label>
                <span class="pb-1.5 text-xs text-[#2F4A3A]/60">Then click Add on a product.</span>
            </div>

            @if ($errors->foodItem->any())
                <div role="alert" class="space-y-1 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs text-red-700">
                    @foreach ($errors->foodItem->all() as $message)
                        <p>{{ $message }}</p>
                    @endforeach
                </div>
            @endif

            <x-product.catalog
                :categories-by-parent="$categoriesByParent"
                :products="$products"
                :product-category-ids="$productCategoryIds"
            />
        </form>
    </dialog>
</div>
