<?php

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Creates a household with one member of the given role.
 *
 * @return array{0: User, 1: Household}
 */
function householdWithMember(string $role): array
{
    $user = User::factory()->create();
    $household = Household::query()->create(['name' => 'Home']);
    $household->members()->create(['user_id' => $user->id, 'role' => $role, 'joined_at' => now()]);

    return [$user, $household];
}
