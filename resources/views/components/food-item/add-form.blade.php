@props(['household'])

{{-- Shelf radios and product "Add" buttons elsewhere on the page submit this form via form="add-food-item". --}}
<form id="add-food-item" method="POST" action="{{ route('households.food-items.store', $household->id) }}" class="mt-8 flex flex-wrap items-end gap-2">
    @csrf

    <label>Price <input name="purchase_price" type="number" min="0" step="0.01" value="{{ old('purchase_price') }}" required class="border p-2"></label>
    <label>Use by <input name="expiration_date" type="date" value="{{ old('expiration_date') }}" required class="border p-2"></label>
    <span class="text-sm text-stone-500">Select a shelf below, then click Add on a product.</span>
</form>

@if (session('food_item_status'))
    <p>{{ session('food_item_status') }}</p>
@endif

@foreach ($errors->getBag('foodItem')->all() as $message)
    <p class="text-red-700">{{ $message }}</p>
@endforeach
