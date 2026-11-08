@props(['products'])

<div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4 border-2 px-2 py-2">
    @foreach($products as $product)
        <x-product.card :product="$product" />
    @endforeach
</div>
