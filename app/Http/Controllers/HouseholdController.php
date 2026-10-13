<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Services\Authenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
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

        return redirect('/households');
    }
}
