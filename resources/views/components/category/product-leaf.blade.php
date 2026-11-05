@props(['household', 'product'])

<li class="flex items-center gap-2 px-1 py-0.5 pl-6 hover:bg-stone-100">
    <span>📄</span>
    <span>{{ $product->name }}</span>
    <span class="text-xs text-stone-500">{{ (float) $product->default_size_g_ml }} {{ $product->measurement_unit }}</span>
    <span>Edit</span>
    <span>
        <form method="POST" action="{{ route('household.products.remove', [$household->id, $product->id]) }}">
            @csrf
            <button type="submit">Delete</button>
        </form>
    </span>
</li>
