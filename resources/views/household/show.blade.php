@php($readOnly ??= false)

<x-household.layout :household="$household" :read-only="$readOnly">
    @if ($readOnly)
        <div>
            <p>
                You are viewing this household in read-only mode.
            </p>
            @empty($currentUser)
                <a href="{{ route('register') }}">
                    Register
                </a>
            @endempty
        </div>
    @endif

    @if (session('food_item_status'))
        <p class="mt-4">{{ session('food_item_status') }}</p>
    @endif

    <x-household.expiring-soon :household="$household" />


    @unless ($readOnly)
            <x-food-item.add-dialog
                :household="$household"
                :shelf-labels="$household->appliances->flatMap->shelves->pluck('label', 'id')"
                :categories-by-parent="$catalogueCategoriesByParent"
                :products="$catalogueProducts"
                :product-category-ids="$productCategoryIds"
            />
        @endunless

    <x-appliance.list :household="$household" :read-only="$readOnly"/>

    @unless($readOnly)
            @can('manage', $household)
        @if($household->share_token)
            <p>
                Share link: <a href="{{ route('households.share-token', $household->share_token) }}" class="underline break-all">
                    {{ route('households.share-token', $household->share_token) }}
                </a>
            </p>
        @endif
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('households.share-token.store', $household->id) }}">
                    @csrf
                    <button type="submit">
                        {{ $household->share_token ? 'Regenerate Share Link' : 'Generate Share Link' }}
                    </button>
                </form>

                @if ($household->share_token)
                    <form method="POST" action="{{ route('households.share-token.destroy', $household->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 border rounded">Disable Sharing</button>
                    </form>
                @endif
            </div>
    @endcan
        @endunless
</x-household.layout>
