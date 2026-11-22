<x-layouts::app>
    <div class="mx-auto max-w-xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold tracking-tight text-[#2F4A3A]">My Profile</h1>

            <a
                href="{{ route('profile.edit') }}"
                class="rounded-full bg-[#2F4A3A] px-4 py-1.5 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
            >
                Edit Profile
            </a>
        </div>

        @if (session('status'))
            <p role="status" class="rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6]/60 px-4 py-2.5 text-sm text-[#2F4A3A]">
                {{ session('status') }}
            </p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-[#2F4A3A]/15 bg-white shadow-sm">
            {{-- Header with initials avatar --}}
            <div class="flex items-center gap-4 border-b border-[#2F4A3A]/10 px-6 py-5">
                <span aria-hidden="true" class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#EFEBF6] text-base font-semibold text-[#2F4A3A]">
                    {{ mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1)) }}
                </span>

                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-[#2F4A3A]">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </p>
                    <p class="truncate text-sm text-[#2F4A3A]/60">{{ $user->email }}</p>
                </div>
            </div>

            {{-- Profile details --}}
            <dl class="divide-y divide-[#2F4A3A]/10 text-sm">
                <div class="flex items-center justify-between gap-4 px-6 py-3.5">
                    <dt class="font-medium text-[#2F4A3A]/60">First Name</dt>
                    <dd class="font-medium text-[#2F4A3A]">{{ $user->first_name }}</dd>
                </div>

                <div class="flex items-center justify-between gap-4 px-6 py-3.5">
                    <dt class="font-medium text-[#2F4A3A]/60">Last Name</dt>
                    <dd class="font-medium text-[#2F4A3A]">{{ $user->last_name }}</dd>
                </div>

                <div class="flex items-center justify-between gap-4 px-6 py-3.5">
                    <dt class="font-medium text-[#2F4A3A]/60">Email</dt>
                    <dd class="font-medium text-[#2F4A3A]">{{ $user->email }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts::app>
