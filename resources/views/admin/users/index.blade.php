<x-layouts::app title="Admin – Users">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#2F4A3A]">
                    User Management
                    <span class="text-base font-normal text-[#2F4A3A]/55">({{ $users->count() }})</span>
                </h1>
                <p class="mt-1 text-sm text-[#2F4A3A]/60">Manage registered accounts and administrator privileges.</p>
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search name or email…"
                    class="w-64 rounded-xl border border-[#2F4A3A]/20 bg-white px-3.5 py-1.5 text-sm text-[#2F4A3A] placeholder:text-[#2F4A3A]/40 focus:border-[#2F4A3A] focus:outline-none focus:ring-2 focus:ring-[#2F4A3A]/15"
                >
                <button
                    type="submit"
                    class="cursor-pointer rounded-full bg-[#2F4A3A] px-4 py-1.5 text-sm font-medium text-white transition hover:bg-[#2F4A3A]/90"
                >
                    Search
                </button>
            </form>
        </div>

        @if (session('status'))
            <p role="status" class="rounded-xl border border-[#2F4A3A]/15 bg-[#EFEBF6]/60 px-4 py-2.5 text-sm text-[#2F4A3A]">
                {{ session('status') }}
            </p>
        @endif

        @if (session('error'))
            <p role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">
                {{ session('error') }}
            </p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-[#2F4A3A]/15 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm text-[#2F4A3A]">
                    <thead>
                    <tr class="border-b border-[#2F4A3A]/10 bg-[#F4F6F4]/70 text-xs font-semibold uppercase tracking-wide text-[#2F4A3A]/65">
                        <th class="px-5 py-3">User</th>
                        <th class="px-4 py-3">Created</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2F4A3A]/10">
                    @forelse ($users as $user)
                        <tr @class([
                                'transition',
                                'bg-[#F4F6F4]/50 opacity-70' => $user->deactivated,
                                'hover:bg-[#F4F6F4]/60' => ! $user->deactivated,
                            ])>
                            {{-- User --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                        <span aria-hidden="true" @class([
                                            'flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold',
                                            'bg-stone-200 text-stone-500' => $user->deactivated,
                                            'bg-[#EFEBF6] text-[#2F4A3A]' => ! $user->deactivated,
                                        ])>
                                            {{ mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1)) }}
                                        </span>

                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-[#2F4A3A]">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                            @if ($user->id === $currentUserId)
                                                <span class="text-xs font-normal text-[#2F4A3A]/55">(you)</span>
                                            @endif
                                        </p>
                                        <p class="truncate text-xs text-[#2F4A3A]/60">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Created --}}
                            <td class="whitespace-nowrap px-4 py-3.5 tabular-nums text-[#2F4A3A]/70">
                                {{ $user->created_at?->format('d.m.Y') ?? '—' }}
                            </td>

                            {{-- Status / Role --}}
                            <td class="whitespace-nowrap px-4 py-3.5">
                                @if ($user->deactivated)
                                    <span class="inline-block rounded-full border border-red-200 bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                            Deactivated
                                        </span>
                                @elseif ($user->is_admin)
                                    <span class="inline-block rounded-full bg-[#2F4A3A] px-2.5 py-0.5 text-xs font-medium text-white">
                                            Admin
                                        </span>
                                @else
                                    <span class="inline-block rounded-full bg-[#F4F6F4] px-2.5 py-0.5 text-xs font-medium text-[#2F4A3A]/75">
                                            User
                                        </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                @if ($user->deactivated)
                                    <span class="text-xs text-[#2F4A3A]/45">Not manageable</span>
                                @elseif ($user->id !== $currentUserId)
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="contents">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="cursor-pointer rounded-full border border-[#6E5D8F]/30 bg-[#EFEBF6]/60 px-3 py-1 text-xs font-medium text-[#6E5D8F] transition hover:border-[#6E5D8F] hover:bg-[#EFEBF6]"
                                            >
                                                {{ $user->is_admin ? 'Revoke admin' : 'Make admin' }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm(@js('Delete user “'.$user->first_name.' '.$user->last_name.'”?'))"
                                            class="contents"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="cursor-pointer rounded-full border border-red-600/30 bg-white px-3 py-1 text-xs font-medium text-red-700 transition hover:border-red-600 hover:bg-red-50"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-[#2F4A3A]/55">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
