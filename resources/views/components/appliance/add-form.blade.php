@props(['household'])

<form method="POST" action="{{ route('households.appliances.store', $household->id) }}" class="mt-8 flex max-w-sm flex-col gap-3">
    @csrf

    <label for="name">Appliance Name</label>
    <input id="name" name="name" type="text" required class="border p-2">

    <label for="appliance_type">Appliance Type</label>
    <select id="appliance_type" name="appliance_type" required class="border p-2">
        <option value="fridge">Fridge</option>
        <option value="freezer">Freezer</option>
        <option value="shelf">Shelf</option>
        <option value="pantry">Pantry</option>
        <option value="cabinet">Cabinet</option>
    </select>

    <button type="submit" class="bg-stone-800 p-2 text-white">Add Appliance</button>
</form>
