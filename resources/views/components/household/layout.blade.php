@props(['household', 'title' => null, 'readOnly' => false])

{{-- Page frame shared by all household tabs: name, tab bar, content. --}}
<x-layouts::app :title="$title ? $household->name.' – '.$title : $household->name">
    <h1 class="text-2xl font-semibold">{{ $household->name }}</h1>

    @unless($readOnly)
    <x-household.tabs :household="$household" />
    @endunless

    {{ $slot }}

    @unless($readOnly)
    <a href="{{ route('households.index') }}" class="mt-8 inline-block underline">Back to my households</a>
    @endunless
</x-layouts::app>
