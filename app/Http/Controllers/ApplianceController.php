<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ApplianceController extends Controller
{
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

        return redirect()->route('households.show', $household->id);
    }
}
