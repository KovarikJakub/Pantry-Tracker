@props(['household', 'parent'])

{{-- Rendered inside the parent node's Alpine scope, which owns the "adding" flag. --}}
<li class="py-1.5 pl-2.5">
    <form
        method="POST"
        action="{{ route('households.categories.store', $household->id) }}"
        class="flex flex-wrap items-center gap-2"
        @keydown.escape="adding = false"
    >
        @csrf
        <input type="hidden" name="parent_id" value="{{ $parent->id }}">

        <input
            name="name"
            value="{{ old('parent_id') === $parent->id ? old('name') : '' }}"
            placeholder="New subcategory of {{ $parent->name }}"
            required
            maxlength="255"
            x-init="$nextTick(() => $el.focus())"
            class="min-w-56 flex-1 max-w-xs rounded-xl border border-[#2F4A3A]/20 bg-white px-3 py-1.5 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
        >

        <button
            type="submit"
            class="cursor-pointer rounded-full bg-[#2F4A3A] px-3.5 py-1.5 text-xs font-medium text-white transition hover:bg-[#2F4A3A]/90"
        >
            Add
        </button>

        <button
            type="button"
            class="cursor-pointer rounded-full px-3 py-1.5 text-xs font-medium text-[#2F4A3A]/70 transition hover:bg-[#F4F6F4] hover:text-[#2F4A3A]"
            @click="adding = false"
        >
            Cancel
        </button>
    </form>

    @if (old('parent_id') === $parent->id)
        @error('name', 'category')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</li>
