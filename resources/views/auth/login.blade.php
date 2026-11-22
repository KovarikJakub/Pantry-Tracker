<x-layouts::app title="Log in">
    @php
        $inputClass = 'w-full rounded-xl border border-[#2F4A3A]/20 bg-white px-3.5 py-2 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15';
    @endphp

    <div class="mx-auto grid max-w-5xl items-center gap-10 py-8 lg:grid-cols-2 lg:gap-16 lg:py-16">
        {{-- Left: Household intro --}}
        <section class="space-y-4">
            <h1 class="text-3xl font-semibold tracking-tight text-[#2F4A3A] sm:text-4xl">
                Keep track of the food in your household.
            </h1>

            <p class="text-base leading-relaxed text-[#2F4A3A]/70 sm:text-lg">
                Organise fridges and freezers by shelf, get alerted before food expires,
                and see what you ate, how much nutrition it gave you and what it cost.
            </p>
        </section>

        {{-- Right: Log in card --}}
        <div class="w-full max-w-md justify-self-center rounded-2xl border border-[#2F4A3A]/15 bg-white p-6 shadow-sm sm:p-8 lg:justify-self-end">
            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <div class="border-b border-[#2F4A3A]/10 pb-4">
                    <h2 class="text-xl font-semibold text-[#2F4A3A]">Log in</h2>
                    <p class="mt-1 text-xs text-[#2F4A3A]/60">Welcome back to your household.</p>
                </div>

                @if (session('status'))
                    <p role="status" class="rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6]/60 px-3.5 py-2 text-xs text-[#2F4A3A]">
                        {{ session('status') }}
                    </p>
                @endif

                <div class="space-y-4">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-[#2F4A3A]">Email</span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="{{ $inputClass }}"
                        >
                    </label>

                    <label class="flex flex-col gap-1">
                        <span class="text-xs font-medium text-[#2F4A3A]">Password</span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="{{ $inputClass }}"
                        >
                    </label>

                    @error('email')
                    <p role="alert" class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs text-red-700">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full cursor-pointer rounded-full bg-[#2F4A3A] px-5 py-2.5 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
                >
                    Log in
                </button>

                <p class="text-center text-xs text-[#2F4A3A]/65">
                    No account yet?
                    <a href="{{ route('register') }}" class="font-medium text-[#2F4A3A] underline underline-offset-2 hover:text-[#6E5D8F]">
                        Register
                    </a>
                </p>
            </form>
        </div>
    </div>
</x-layouts::app>
