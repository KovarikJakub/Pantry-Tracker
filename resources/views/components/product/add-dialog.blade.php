@props(['household', 'categoryNames'])

{{--
    One shared modal for adding a product to any category of the tree. Category rows open it by dispatching
    the window event "open-add-product" with { id, name }.
    Backend (not implemented yet): POST /households/{id}/products, error bag "product",
    redirect back to the catalogue with "opened_category_id".
--}}

@php
    $hasErrors = $errors->product->any();
    $failedCategoryId = $hasErrors ? old('category_id') : null;

    /** @var array<string, string> $nutritionFields input name => label */
    $nutritionFields = [
        'calories_per_100g' => 'Energy (kcal)',
        'protein_per_100g' => 'Protein (g)',
        'fat_per_100g' => 'Fat (g)',
        'carbs_per_100g' => 'Carbohydrates (g)',
        'fiber_per_100g' => 'Fibre (g)',
    ];
@endphp

<div
    x-data="{
        categoryId: @js($failedCategoryId),
        categoryName: @js($failedCategoryId ? $categoryNames->get($failedCategoryId) : null),
        unit: @js(old('measurement_unit', 'g')),
        open(category) {
            this.categoryId = category.id;
            this.categoryName = category.name;
            this.unit = 'g';
            this.$refs.form.querySelectorAll('input:not([type=hidden])').forEach((input) => input.value = '');
            this.$refs.dialog.showModal();
        },
    }"
    x-init="@js($hasErrors) && $refs.dialog.showModal()"
    @open-add-product.window="open($event.detail)"
>
    <dialog
        x-ref="dialog"
        aria-labelledby="add-product-title"
        class="m-auto w-full max-w-lg border bg-white p-0 text-sm shadow-xl backdrop:bg-black/40"
        @click.self="$refs.dialog.close()"
    >
        <form x-ref="form" method="POST" action="{{ route('households.products.store', $household->id) }}" class="space-y-4 p-6">
            @csrf
            <input type="hidden" name="category_id" :value="categoryId">

            <h2 id="add-product-title" class="text-lg font-semibold">
                Add product to <span x-text="categoryName"></span>
            </h2>

            <fieldset class="grid grid-cols-2 gap-3">
                <legend class="mb-2 font-medium">Product</legend>

                <label class="col-span-2 flex flex-col gap-1">
                    Name
                    <input name="name" value="{{ old('name') }}" required maxlength="255" autofocus class="border px-2 py-1">
                    @error('name', 'product') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="col-span-2 flex flex-col gap-1">
                    <span>Barcode <span class="text-stone-500">(optional)</span></span>
                    <input name="barcode" value="{{ old('barcode') }}" maxlength="255" inputmode="numeric" class="border px-2 py-1">
                    @error('barcode', 'product') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    Package size
                    <input name="default_size_g_ml" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('default_size_g_ml') }}" required class="border px-2 py-1">
                    @error('default_size_g_ml', 'product') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    Unit
                    <select name="measurement_unit" x-model="unit" class="border px-2 py-1">
                        <option value="g">g</option>
                        <option value="ml">ml</option>
                    </select>
                    @error('measurement_unit', 'product') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="col-span-2 flex flex-col gap-1">
                    Default price
                    <input name="default_price" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('default_price') }}" required class="border px-2 py-1">
                    @error('default_price', 'product') <span class="text-red-600">{{ $message }}</span> @enderror
                </label>
            </fieldset>

            <fieldset class="grid grid-cols-2 gap-3">
                <legend class="mb-2 font-medium">Nutrition per 100 <span x-text="unit"></span></legend>

                @foreach ($nutritionFields as $field => $label)
                    <label class="flex flex-col gap-1">
                        {{ $label }}
                        <input name="{{ $field }}" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old($field) }}" required class="border px-2 py-1">
                        @error($field, 'product') <span class="text-red-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
            </fieldset>

            @error('category_id', 'product') <p class="text-red-600">{{ $message }}</p> @enderror

            <div class="flex justify-end gap-3">
                <button type="button" class="underline" @click="$refs.dialog.close()">Cancel</button>
                <button type="submit" class="bg-stone-800 px-3 py-1 text-white">Add product</button>
            </div>
        </form>
    </dialog>
</div>
