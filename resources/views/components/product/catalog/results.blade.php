@props(['products'])

<section aria-live="polite" class="flex min-w-0 flex-1 flex-col">
    <h3 class="mb-3 text-sm font-semibold text-[#2F4A3A]" x-text="heading"></h3>

    <div class="grid min-h-0 flex-1 content-start grid-cols-2 gap-3 overflow-y-auto pr-1 sm:grid-cols-3">
        @foreach ($products as $product)
            <x-product.card :product="$product" x-show="isVisible('{{ $product->id }}')" />
        @endforeach

        <p x-show="! hasResults" class="col-span-full py-8 text-center text-sm text-[#2F4A3A]/50">
            No products found.
        </p>
    </div>
</section>
