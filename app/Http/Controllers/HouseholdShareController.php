<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class HouseholdShareController extends Controller
{
    /**
     * Shows a read only view of the household's appliances and food items for guests.
     */
    public function show(string $token): View
    {
        $household = Household::query()
            ->where('share_token', $token)
            ->firstOrFail();

        $household->load([
            'appliances.shelves.foodItems' => fn ($items) => $items->where('status', 'stored'),
            'appliances.shelves.foodItems.product',
        ]);

        return view('household.show', [
            'household' => $household,
            'readOnly' => true,
        ]);
    }

    /**
     * Generates or regenerates the read-only guest share token for the household.
     * For household managers only.
     */
    public function store(string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $household->share_token = Str::random(32);
        $household->save();

        return back();
    }

    /**
     * Revokes the household's share token.
     * For household managers only.
     */
    public function destroy(string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $household->share_token = null;
        $household->save();

        return back();
    }
}
