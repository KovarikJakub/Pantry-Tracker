@props(['label', 'icon'])

{{-- Icon-only submit button; the label appears as a tooltip on hover or keyboard focus. --}}

<button
    aria-label="{{ $label }}"
    {{ $attributes->merge([ 'type' => 'submit',
                            'class' => 'group/action relative inline-flex size-7 items-center justify-center rounded text-stone-600 hover:bg-stone-200 hover:text-stone-900',
                            ]) }}
>
    <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        @switch($icon)
            @case('eat')
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" />
                <path d="M7 2v20" />
                <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" />
                @break
            @case('trash')
                <path d="M3 6h18" />
                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                <path d="M10 11v6" />
                <path d="M14 11v6" />
                @break
            @case('remove')
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
                @break
        @endswitch
    </svg>

    <span aria-hidden="true" class="invisible absolute bottom-full left-1/2 z-10 mb-1 -translate-x-1/2 rounded bg-stone-800 px-2 py-0.5 text-xs whitespace-nowrap text-white group-hover/action:visible group-focus-visible/action:visible">{{ $label }}</span>
</button>
