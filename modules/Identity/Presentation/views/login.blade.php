<x-layouts.app title="Log in">
    <form method="POST" action="{{ route('login.store') }}" class="flex max-w-sm flex-col gap-3">
        @csrf

        <h1 class="text-2xl font-semibold">Log in</h1>

        @if (session('status'))
            <p class="text-green-700">{{ session('status') }}</p>
        @endif

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="border p-2">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required class="border p-2">

        @error('email')
            <p class="text-red-700">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-stone-800 p-2 text-white">Log in</button>

        <a href="{{ route('register') }}" class="underline">No account yet? Register</a>
    </form>
</x-layouts.app>
