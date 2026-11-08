@props(['household', 'parent'])

{{-- Rendered inside the parent node's Alpine scope, which owns the "adding" flag. --}}
<li class="py-1 pl-6">
    <form
        method="POST"
        action="{{ route('households.categories.store', $household->id) }}"
        class="flex items-center gap-2"
        @keydown.escape="adding = false"
    >
        @csrf
        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
        <span>📁</span>
        <input
            name="name"
            value="{{ old('parent_id') === $parent->id ? old('name') : '' }}"
            placeholder="New subcategory of {{ $parent->name }}"
            required
            maxlength="255"
            x-init="$nextTick(() => $el.focus())"
            class="border px-2 py-0.5"
        >
        <button type="submit" class="bg-stone-800 px-2 py-0.5 text-white">Add</button>
        <button type="button" class="underline" @click="adding = false">Cancel</button>
    </form>

    @if (old('parent_id') === $parent->id)
        @error('name', 'category')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</li>
