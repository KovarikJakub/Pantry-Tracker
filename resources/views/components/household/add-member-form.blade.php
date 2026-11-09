{{-- Rendered inside the household.member-list Livewire component, which handles "email" and "add". --}}
<form wire:submit="add" class="mt-8 flex max-w-sm flex-col gap-3">
    <label for="email">Add user by email</label>
    <input wire:model="email" id="email" type="email" required class="border p-2">

    <button type="submit" class="bg-stone-800 p-2 text-white">Add user</button>
</form>
