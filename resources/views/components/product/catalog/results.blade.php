@props(['products'])

<section aria-live="polite" class="flex min-w-0 flex-1 flex-col">
    <h3 class="mb-2 font-medium" x-text="heading"></h3>

    <div class="grid min-h-0 flex-1 content-start grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-3">
        @foreach ($products as $product)
            <x-product.card :product="$product" x-show="isVisible('{{ $product->id }}')" />
        @endforeach

        <p x-show="! hasResults" class="col-span-full text-stone-500">No products found.</p>
    </div>
</section>
