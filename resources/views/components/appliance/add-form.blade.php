@props(['household'])

<section class="mt-10 max-w-2xl rounded-xl border border-[#2F4A3A]/15 bg-white p-5 shadow-sm">
    <h2 class="text-base font-semibold text-[#2F4A3A]">
        Add appliance
    </h2>
    <form method="POST" action="{{ route('households.appliances.store', $household->id) }}"
          class="mt-4 flex flex-wrap items-end gap-3">
        @csrf

        <div class="min-w-48 flex-1">
            <label for="name" class="block text-xs font-medium text-[#2F4A3A]/75">Appliance name</label>
            <input id="name" name="name" type="text" required placeholder="Garage freezer"
                   class="mt-1 w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3.5 py-2 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
            >
        </div>

        <div class="w-40">
            <label for="appliance_type" class="block text-xs font-medium text-[#2F4A3A]/75">Appliance Type</label>
            <select id="appliance_type" name="appliance_type" required class="mt-1 w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3.5 py-2 text-sm text-[#2F4A3A] focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
            >
                <option value="fridge">Fridge</option>
                <option value="freezer">Freezer</option>
                <option value="shelf">Shelf</option>
                <option value="pantry">Pantry</option>
                <option value="cabinet">Cabinet</option>
            </select>
        </div>
        <button type="submit" class="cursor-pointer rounded-full bg-[#2F4A3A] px-5 py-2 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
        >
            Add Appliance
        </button>
    </form>
</section>
