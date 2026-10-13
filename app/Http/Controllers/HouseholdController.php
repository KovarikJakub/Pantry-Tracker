<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Services\Authenticator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
    /**
     * Lists the households the current user is a member of.
     */
    public function index(Authenticator $authenticator): View
    {
        $userId = $authenticator->user()->id;

        $households = Household::query()
            ->whereHas('members', fn (Builder $members) => $members->where('user_id', $userId))
            ->orderBy('name')
            ->get();

        return view('household.household', ['households' => $households]);
    }

    /**
     * Creates a household and makes the current user its manager.
     */
    public function store(Request $request, Authenticator $authenticator): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $authenticator): void {
            $household = Household::query()->create(['name' => $validated['name']]);

            $household->members()->create([
                'user_id' => $authenticator->user()->id,
                'role' => 'manager',
                'joined_at' => now(),
            ]);
        });

        return redirect()->route('households.index');
    }

    public function show(string $id): View
    {
        $household = Household::query()->whereKey($id)->first();

        return view('household.show', ['household' => $household]);
    }
}
