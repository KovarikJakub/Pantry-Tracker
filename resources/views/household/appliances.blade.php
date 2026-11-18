<x-household.layout :household="$household" title="Appliances">
    <x-appliance.add-form :household="$household" />

    <livewire:appliance.manage-list :household="$household" />
</x-household.layout>
