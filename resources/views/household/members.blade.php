<x-household.layout :household="$household" title="Members">
    <x-household.member-list :members="$household->memberUsers" />

    <x-household.add-member-form :household="$household" />
</x-household.layout>
