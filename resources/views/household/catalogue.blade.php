<x-layouts::app :title="$household->name.' catalogue'">
    <h1 class="text-2xl font-semibold">{{ $household->name }} – category catalogue</h1>

    <x-category.tree :categories-by-parent="$categoriesByParent" :products-by-category="$productsByCategory" />

    <a href="{{ route('households.show', $household->id) }}" class="mt-4 inline-block underline">Back to household</a>
</x-layouts::app>
