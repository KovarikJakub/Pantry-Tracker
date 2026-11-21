<x-layouts::app>
    <div>
        <h1>Edit Profile</h1>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div>
                <label for="first_name">First Name</label>
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    value="{{ old('first_name', $user->first_name) }}"
                    required
                >
                @error('first_name')
                <div>{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    value="{{ old('last_name', $user->last_name) }}"
                    required
                >
                @error('last_name')
                <div>{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >
                @error('email')
                <div>{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Save Changes</button>
            <a href="{{ route('profile.show') }}">Cancel</a>
        </form>
    </div>
</x-layouts::app>
