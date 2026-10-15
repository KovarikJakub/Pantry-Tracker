<x-layouts.app title="Register">
    <form method="POST" action="{{ route('register.store') }}" class="flex max-w-sm flex-col gap-3">
        @csrf

        <h1 class="text-2xl font-semibold">Register</h1>

        <label for="first_name">First name</label>
        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autofocus class="border p-2">

        <label for="last_name">Last name</label>
        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required class="border p-2">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="border p-2">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required class="border p-2">

        @error('email')
            <p class="text-red-700">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-stone-800 p-2 text-white">Register</button>

        <a href="{{ route('login') }}" class="underline">Already have an account? Log in</a>
    </form>
</x-layouts.app>
