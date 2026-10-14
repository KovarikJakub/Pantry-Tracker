<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\User;
use App\Services\Authenticator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function show(string $id, Authenticator $authenticator): View
    {
        $household = Household::query()->with('appliances.shelves')->whereKey($id)->first();

        return view('household.show', [
            'household' => $household,
            'isManager' => $household->isManagedBy($authenticator->user()->id),
        ]);
    }

    /**
     * Adds an appliance to the household. Managers only.
     */
    public function addAppliance(Request $request, Authenticator $authenticator, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

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

    /**
     * Adds a shelf at the end of one of the household's appliances. Managers only.
     */
    public function addShelf(Request $request, Authenticator $authenticator, string $id, string $applianceId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

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

        return redirect()->route('households.show', $household->id);
    }

    /**
     * Adds an existing user, found by email, to the household. Managers only.
     */
    public function addMember(Request $request, Authenticator $authenticator, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $user = User::query()->where('email', (string) $request->input('email'))->first();

        if ($user === null) {
            $status = 'User does not exist.';
        } elseif ($household->members()->where('user_id', $user->id)->exists()) {
            $status = 'User is already in the household.';
        } else {
            $household->members()->create([
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
            $status = 'User was added.';
        }

        return redirect()->route('households.show', $household->id)->with('status', $status);
    }
}
