<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShelfController extends Controller
{
    /**
     * Adds a shelf at the end of one of the household's appliances. Managers only.
     */
    public function store(Request $request, string $id, string $applianceId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $appliance = $household->appliances()->findOrFail($applianceId);

        // A separate error bag per appliance keeps errors under the form that was submitted.
        $validated = $request->validateWithBag('shelf_'.$appliance->id, [
            'label' => ['required', 'string', 'max:255'],
            'max_load_capacity_g' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ]);

        $appliance->shelves()->create([
            'label' => $validated['label'],
            'shelf_order' => ($appliance->shelves()->max('shelf_order') ?? 0) + 1,
            'max_load_capacity_g' => $validated['max_load_capacity_g'],
        ]);

        return redirect()->route('households.appliances.index', $household->id);
    }
}
