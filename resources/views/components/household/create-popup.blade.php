<div
    x-data="{ open: @js($errors->has('name')) }"
    @keydown.escape.window="open = false"
    x-effect="if (open) $nextTick(() => $refs.nameInput.focus())"
>
    {{-- Secondary trigger button --}}
    <button
        type="button"
        @click="open = true"
        class="cursor-pointer rounded-full border border-[#2F4A3A]/30 bg-transparent px-4 py-1.5 text-sm font-medium text-[#2F4A3A] transition hover:border-[#2F4A3A] hover:bg-[#EFEBF6]/70"
    >
        Create new household
    </button>

    {{-- Modal --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition.opacity
            @click.self="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-xs"
            style="display: none;"
        >
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-[#2F4A3A]">Create new household</h2>

                <form method="POST" action="{{ route('households.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="new-household-name" class="block text-sm font-medium text-[#2F4A3A]">
                            Name
                        </label>
                        <input
                            x-ref="nameInput"
                            id="new-household-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            placeholder="e.g. Weekend cabin"
                            class="mt-1.5 w-full rounded-xl border border-[#2F4A3A]/25 bg-white px-3.5 py-2 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/20"
                        >
                        @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button
                            type="button"
                            @click="open = false"
                            class="cursor-pointer rounded-xl px-4 py-2 text-sm font-medium text-[#2F4A3A]/70 transition hover:bg-[#F4F6F4] hover:text-[#2F4A3A]"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-xl bg-[#2F4A3A] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
                        >
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
