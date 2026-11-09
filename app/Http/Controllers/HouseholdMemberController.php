<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class HouseholdMemberController extends Controller
{
    /**
     * Shows the household's members tab. Managers only.
     */
    public function index(string $id): View
    {
        $household = Household::query()->findOrFail($id);

        Gate::authorize('manage', $household);

        return view('household.members', ['household' => $household]);
    }
}
