@php
    $hasErrors = $errors->editProduct->any();

    /** @var array<string, array{label: string, max: int}> $nutritionFields */
    $nutritionFields = [
        'calories_per_100g' => ['label' => 'Energy (kcal)', 'max' => 900],
        'protein_per_100g' => ['label' => 'Protein (g)', 'max' => 100],
        'fat_per_100g' => ['label' => 'Fat (g)', 'max' => 100],
        'carbs_per_100g' => ['label' => 'Carbohydrates (g)', 'max' => 100],
        'fiber_per_100g' => ['label' => 'Fibre (g)', 'max' => 100],
    ];

    $inputClass = 'w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15';
@endphp

<div
    x-data="{
        action: @js($hasErrors ? old('_action') : ''),
        productName: @js($hasErrors ? old('name') : ''),
        unit: @js($hasErrors ? old('measurement_unit', 'g') : 'g'),
        macroError: '',
        open(product) {
            this.action = product.url;
            this.productName = product.name;
            this.unit = product.measurement_unit ?? 'g';
            this.macroError = '';
            Object.entries(product).forEach(([key, value]) => {
                const input = this.$refs.form.elements.namedItem(key);
                if (input && input.type !== 'hidden') {
                    input.value = value ?? '';
                }
            });
            document.body.classList.add('overflow-hidden');
            this.$refs.dialog.showModal();
        },
        unlockScroll() {
            document.body.classList.remove('overflow-hidden');
        },
    }"
    x-init="if (@js($hasErrors)) { document.body.classList.add('overflow-hidden'); $refs.dialog.showModal(); }"
    @open-edit-product.window="open($event.detail)"
>
    <dialog
        x-ref="dialog"
        aria-labelledby="edit-product-title"
        @click.self="$refs.dialog.close()"
        @close="unlockScroll()"
        class="m-auto max-h-[90vh] w-[min(34rem,calc(100vw-2rem))] overflow-y-auto rounded-2xl border border-[#2F4A3A]/15 bg-white p-0 text-sm text-[#2F4A3A] shadow-2xl backdrop:bg-black/40 backdrop:backdrop-blur-xs"
    >
        <form x-ref="form" method="POST" :action="action" class="space-y-5 p-6">
            @csrf
            @method('PATCH')
            <input type="hidden" name="_action" :value="action">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-4 border-b border-[#2F4A3A]/10 pb-4">
                <h2 id="edit-product-title" class="flex flex-wrap items-center gap-2 text-lg font-semibold text-[#2F4A3A]">
                    <span>Edit</span>
                    <span x-text="productName" class="rounded-full bg-[#EFEBF6] px-3 py-0.5 text-sm font-medium text-[#2F4A3A]"></span>
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
            @if ($errors->editProduct->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs text-red-700">
                    <ul class="list-disc space-y-0.5 pl-4">
                        @foreach ($errors->editProduct->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Product details --}}
            <fieldset class="grid grid-cols-2 gap-3">
                <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#2F4A3A]/60">Product</legend>

                <label class="col-span-2 flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Name</span>
                    <input name="name" value="{{ old('name') }}" required maxlength="255" autofocus class="{{ $inputClass }}">
                    @error('name', 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="col-span-2 flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Barcode <span class="font-normal text-[#2F4A3A]/55">(optional)</span></span>
                    <input name="barcode" value="{{ old('barcode') }}" maxlength="255" inputmode="numeric" class="{{ $inputClass }}">
                    @error('barcode', 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Package size</span>
                    <input name="default_size_g_ml" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('default_size_g_ml') }}" required class="{{ $inputClass }}">
                    @error('default_size_g_ml', 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Unit</span>
                    <div class="relative">
                        <select name="measurement_unit" x-model="unit" class="{{ $inputClass }} appearance-none pr-8">
                            <option value="g">g</option>
                            <option value="ml">ml</option>
                        </select>
                        <svg aria-hidden="true" class="pointer-events-none absolute right-2.5 top-1/2 size-4 -translate-y-1/2 text-[#2F4A3A]/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7 15 5 5 5-5" /><path d="m7 9 5-5 5 5" />
                        </svg>
                    </div>
                    @error('measurement_unit', 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Default price</span>
                    <input name="default_price" type="number" min="0" step="0.01" inputmode="decimal" value="{{ old('default_price') }}" required class="{{ $inputClass }}">
                    @error('default_price', 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-[#2F4A3A]">Default expiry (days)</span>
                    <input name="default_expiry_days" type="number" min="1" max="65535" step="1" inputmode="numeric" value="{{ old('default_expiry_days') }}" required class="{{ $inputClass }}">
                    @error('default_expiry_days', 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
            </fieldset>

            {{-- Nutrition --}}
            <fieldset class="grid grid-cols-2 gap-3 rounded-xl bg-[#F4F6F4] p-4">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-[#2F4A3A]/60">
                    Nutrition per 100 <span x-text="unit"></span>
                </legend>

                @foreach ($nutritionFields as $field => $config)
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-[#2F4A3A]">{{ $config['label'] }}</span>
                        <input
                            name="{{ $field }}"
                            type="number"
                            min="0"
                            max="{{ $config['max'] }}"
                            step="0.01"
                            inputmode="decimal"
                            value="{{ old($field) }}"
                            required
                            class="{{ $inputClass }}"
                        >
                        @error($field, 'editProduct') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
                <p x-show="macroError" x-text="macroError" class="col-span-2 text-xs text-red-600"></p>
            </fieldset>

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
                    class="cursor-pointer rounded-full bg-[#2F4A3A] px-5 py-2 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
                >
                    Save changes
                </button>
            </div>
        </form>
    </dialog>
</div>
