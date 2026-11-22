<x-layouts::app>
    @php
        $inputClass = 'w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3.5 py-2 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15';
    @endphp

    <div class="mx-auto max-w-lg">
        <div class="overflow-hidden rounded-2xl border border-[#2F4A3A]/15 bg-white shadow-sm">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5 p-6">
                @csrf
                @method('PATCH')

                {{-- Header --}}
                <div class="flex items-center justify-between gap-4 border-b border-[#2F4A3A]/10 pb-4">
                    <h1 class="text-lg font-semibold text-[#2F4A3A]">Edit Profile</h1>

                    <a
                        href="{{ route('profile.show') }}"
                        aria-label="Close"
                        class="flex size-8 items-center justify-center rounded-full text-lg text-[#2F4A3A]/60 transition hover:bg-[#F4F6F4] hover:text-[#2F4A3A]"
                    >
                        &times;
                    </a>
                </div>

                {{-- Fields --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-[#2F4A3A]">First Name</span>
                        <input
                            id="first_name"
                            name="first_name"
                            type="text"
                            value="{{ old('first_name', $user->first_name) }}"
                            required
                            autofocus
                            class="{{ $inputClass }}"
                        >
                        @error('first_name')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-[#2F4A3A]">Last Name</span>
                        <input
                            id="last_name"
                            name="last_name"
                            type="text"
                            value="{{ old('last_name', $user->last_name) }}"
                            required
                            class="{{ $inputClass }}"
                        >
                        @error('last_name')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col gap-1 sm:col-span-2">
                        <span class="text-xs font-medium text-[#2F4A3A]">Email</span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="{{ $inputClass }}"
                        >
                        @error('email')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                {{-- Footer actions --}}
                <div class="flex items-center justify-end gap-2 border-t border-[#2F4A3A]/10 pt-4">
                    <a
                        href="{{ route('profile.show') }}"
                        class="rounded-full px-4 py-2 text-sm font-medium text-[#2F4A3A]/70 transition hover:bg-[#F4F6F4] hover:text-[#2F4A3A]"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="cursor-pointer rounded-full bg-[#2F4A3A] px-5 py-2 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
