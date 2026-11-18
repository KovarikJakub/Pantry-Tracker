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
            class="min-w-0 flex-1 border border-stone-300 bg-white px-2 py-1 text-sm"
        >


        <div class="flex w-48 shrink-0 justify-end">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="add"
                class="cursor-pointer bg-stone-800 px-3 py-1 text-sm text-white hover:bg-stone-700 disabled:cursor-wait disabled:opacity-50"
            >Add</button>
        </div>
    </form>
</li>
