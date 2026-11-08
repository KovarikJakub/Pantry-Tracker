<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ApplianceController extends Controller
{
    /**
     * Shows the household's appliances tab, where appliances and shelves are added. Managers only.
     */
    public function index(string $id): View
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $household->load('appliances.shelves');

        return view('household.appliances', ['household' => $household]);
    }

    /**
     * Adds an appliance to the household. Managers only.
     */
    public function store(Request $request, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'appliance_type' => ['required', Rule::in(['fridge', 'freezer', 'shelf', 'pantry', 'cabinet'])],
        ]);

        $household->appliances()->create([
            'name' => $validated['name'],
            'appliance_type' => $validated['appliance_type'],
        ]);

        return redirect()->route('households.appliances.index', $household->id);
    }
}
