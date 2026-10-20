<x-layouts.app :title="$household->name">
    <h1 class="text-2xl font-semibold">{{ $household->name }}</h1>

    @if ($isManager)
        <x-household.add-member-form :household="$household" />
        <x-appliance.add-form :household="$household" />
    @endif

    <x-product.catalog :products="$household->products" />

    <x-appliance.list :household="$household" :is-manager="$isManager" />

    <a href="{{ route('households.index') }}" class="mt-4 inline-block underline">Back to my households</a>
</x-layouts.app>
