{{-- The last row of the member list, rendered inside the household.member-list Livewire component, which handles
     "email" and "add". The input and button line up with the name and actions columns above. --}}
<li class="bg-stone-50 px-4 py-3">
    <form wire:submit="add" class="flex items-center gap-3">
        <span aria-hidden="true" class="flex size-10 shrink-0 items-center justify-center rounded-full border border-dashed border-stone-300 text-lg text-stone-400">+</span>

        <input
            wire:model="email"
            id="email"
            type="email"
            placeholder="Add user by email"
            aria-label="Add user by email"
            required
            class="w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3.5 py-1.5 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
        >


        <div class="flex w-48 shrink-0 justify-end">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="add"
                class="cursor-pointer rounded-full bg-[#2F4A3A] px-4 py-1.5 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
            >Add</button>
        </div>
    </form>
</li>
