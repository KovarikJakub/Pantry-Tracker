@props(['household', 'title' => null])

{{-- Page frame shared by all household tabs: name, tab bar, content. --}}
<x-layouts::app :title="$title ? $household->name.' – '.$title : $household->name">
    <h1 class="text-2xl font-semibold">{{ $household->name }}</h1>

    <x-household.tabs :household="$household" />

    {{ $slot }}

    <a href="{{ route('households.index') }}" class="mt-8 inline-block underline">Back to my households</a>
</x-layouts::app>
