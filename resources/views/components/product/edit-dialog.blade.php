@props(['household', 'categoryNames'])

{{--
    One shared modal for editing any product in the catalogue tree. Product rows open it by dispatching
    the window event "open-edit-product" with { url, name, barcode, default_size_g_ml, measurement_unit,
    default_price, calories_per_100g, protein_per_100g, fat_per_100g, carbs_per_100g, fiber_per_100g }.
    Backend: PATCH /households/{id}/products/{productId}, error bag "editProduct",
    redirect back to the catalogue with "opened_category_id".
--}}

@php
    $hasErrors = $errors->product->any();

    /** @var array<string, string> $nutritionFields input name => label */
    $nutritionFields = [
        'calories_per_100g' => 'Energy (kcal)',
        'protein_per_100g' => 'Protein (g)',
        'fat_per_100g' => 'Fat (g)',
        'carbs_per_100g' => 'Carbohydrates (g)',
        'fiber_per_100g' => 'Fibre (g)',
    ];
@endphp

{{--
    x-data creates three reactive variables
--}}
<div
    x-data="{
        action: @js($hasErrors ? old('_action') : ''),
        productName: @js($hasErrors ? old('name') : ''),
        unit: @js($hasErrors ? old('measurement_unit', 'g') : 'g'),
        open(product) {
            this.action = product.url;
            this.productName = product.name;
            this.unit = product.measurement_unit ?? 'g';
            Object.entries(product).forEach(([key, value]) => {
                const input = this.$refs.form.elements.namedItem(key);
                if (input && input.type !== 'hidden') {
                    input.value = value ?? '';
                }
            });
            this.$refs.dialog.showModal();
        },
    }"
    x-init="@js($hasErrors) && $refs.dialog.showModal()"
    {{--
        Listens for the open-edit-product event dispatched by any Edit button on the page,
        and passes that product's data ($event.detail) into open().
    --}}
    @open-edit-product.window="open($event.detail)"
>
    <dialog
        x-ref="dialog"
        aria-labelledby="edit-product-title"
        class="m-auto w-full max-w-lg border bg-white p-0 text-sm shadow-xl backdrop:bg-black/40"
        @click.self="$refs.dialog.close()"
    >
        <form x-ref="form" method="POST" :action="action" class="space-y-4 p-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="_action" :value="action">

            <h2 id="edit-product-title" class="text-lg font-semibold">
                Edit <span x-text="productName"></span>
            </h2>

            <fieldset class="grid grid-cols-2 gap-3">
                <legend class="mb-2 font-medium">Product</legend>

                <label class="col-span-2 flex flex-col gap-1">
                    Name
                    <input name="name" value="{{ old('name') }}" required maxlength="255" autofocus class="border px-2 py-1">
                    @error('name', 'editProduct') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="col-span-2 flex flex-col gap-1">
                    <span>Barcode <span class="text-stone-500">(optional)</span></span>
                    <input name="barcode" value="{{ old('barcode') }}" maxlength="255" inputmode="numeric" class="border px-2 py-1">
                    @error('barcode', 'editProduct') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    Package size
                    <input name="default_size_g_ml" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('default_size_g_ml') }}" required class="border px-2 py-1">
                    @error('default_size_g_ml', 'editProduct') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    Unit
                    <select name="measurement_unit" x-model="unit" class="border px-2 py-1">
                        <option value="g">g</option>
                        <option value="ml">ml</option>
                    </select>
                    @error('measurement_unit', 'editProduct') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="col-span-2 flex flex-col gap-1">
                    Default price
                    <input name="default_price" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('default_price') }}" required class="border px-2 py-1">
                    @error('default_price', 'editProduct') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>
            </fieldset>

            <fieldset class="grid grid-cols-2 gap-3">
                <legend class="mb-2 font-medium">Nutrition per 100 <span x-text="unit"></span></legend>

                @foreach ($nutritionFields as $field => $label)
                    <label class="flex flex-col gap-1">
                        {{ $label }}
                        <input name="{{ $field }}" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old($field) }}" required class="border px-2 py-1">
                        @error($field, 'editProduct') <span class="text-red-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
            </fieldset>

            <div class="flex justify-end gap-3">
                <button type="button" class="underline" @click="$refs.dialog.close()">Cancel</button>
                <button type="submit" class="bg-stone-800 px-3 py-1 text-white">Save changes</button>
            </div>
        </form>
    </dialog>
</div>
