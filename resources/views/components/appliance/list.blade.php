@props(['household', 'readOnly' => false])

@if ($household->appliances->isEmpty())
    <p class="mt-4">There are no appliances in this household.</p>
@else
    <div class="mt-6 space-y-10">
        @foreach ($household->appliances as $appliance)
            <section class="space-y-4">
                <h2 class="text-xl font-semibold tracking-tight text-[#2F4A3A]">
                    {{ $appliance->name }}
                    <span class="rounded-full bg-[#EFEBF6] ml-3 px-3 py-0.5 text-xs font-medium text-[#2F4A3A]">
                        {{ $appliance->appliance_type }}
                    </span>
                </h2>
                <x-appliance.card :household="$household" :appliance="$appliance" :read-only="$readOnly"/>
            </section>
        @endforeach
    </div>
@endif
