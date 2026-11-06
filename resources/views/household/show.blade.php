<x-layouts::app :title="$household->name">
    <h1 class="text-2xl font-semibold">{{ $household->name }}</h1>

    @can('manage', $household)
        <a href="{{ route('households.catalogue', $household->id) }}" class="mt-2 inline-block underline">Manage category catalogue</a>
        <x-household.add-member-form :household="$household" />
        <x-appliance.add-form :household="$household" />
    @endcan

    <x-food-item.add-form :household="$household" />
    <x-product.catalog :products="$household->products" />

    <x-appliance.list :household="$household" />

    <a href="{{ route('households.index') }}" class="mt-4 inline-block underline">Back to my households</a>
</x-layouts::app>
