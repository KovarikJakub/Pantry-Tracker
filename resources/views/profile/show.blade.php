<x-layouts::app>
    <div>
        <h1>My Profile</h1>

        @if (session('status'))
            <div>{{ session('status') }}</div>
        @endif

        <p><strong>First Name:</strong> {{ $user->first_name }}</p>
        <p><strong>Last Name:</strong> {{ $user->last_name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>

        <a href="{{ route('profile.edit') }}">Edit Profile</a>
    </div>
</x-layouts::app>
