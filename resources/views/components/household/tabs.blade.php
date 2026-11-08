@props(['household'])

{{-- Regular members only have the Shelves tab, so the bar is shown to managers only. --}}
@can('manage', $household)
    @php
        /** @var array<string, string> $tabs route name => label */
        $tabs = [
            'households.show' => 'Shelves',
            'households.members.index' => 'Members',
            'households.appliances.index' => 'Appliances',
            'households.catalogue' => 'Catalogue',
        ];
    @endphp

    <nav aria-label="Household sections" class="mt-4 flex gap-4 border-b border-stone-200 text-sm">
        @foreach ($tabs as $routeName => $label)
            @php($isActive = request()->routeIs($routeName))
            <a
                href="{{ route($routeName, $household->id) }}"
                @if ($isActive) aria-current="page" @endif
                @class([
                    '-mb-px border-b-2 px-1 py-2',
                    'border-stone-800 font-semibold' => $isActive,
                    'border-transparent text-stone-500 hover:text-stone-800' => ! $isActive,
                ])
            >{{ $label }}</a>
        @endforeach
    </nav>
@endcan
