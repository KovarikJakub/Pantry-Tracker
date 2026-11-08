@props(['products'])

<div class="grid max-h-[60vh] grid-cols-2 gap-3 overflow-y-auto border-2 px-2 py-2 sm:grid-cols-4">
    @foreach($products as $product)
        <x-product.card :product="$product" />
    @endforeach
</div>
