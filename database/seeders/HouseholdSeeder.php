<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a demo household managed by admin@example.com, with user@example.com as a member,
 * two appliances and their shelves. Depends on UserSeeder.
 */
class HouseholdSeeder extends Seeder
{
    private const APPLIANCES = [
        [
            'name' => 'Kitchen fridge',
            'appliance_type' => 'fridge',
            'shelves' => [
                ['label' => 'Top shelf', 'max_load_capacity_g' => 5000],
                ['label' => 'Middle shelf', 'max_load_capacity_g' => 8000],
                ['label' => 'Vegetable drawer', 'max_load_capacity_g' => 6000],
            ],
        ],
        [
            'name' => 'Basement freezer',
            'appliance_type' => 'freezer',
            'shelves' => [
                ['label' => 'Upper drawer', 'max_load_capacity_g' => 10000],
                ['label' => 'Lower drawer', 'max_load_capacity_g' => 15000],
            ],
        ],
    ];

    public function run(): void
    {
        $household = Household::query()->firstOrCreate(['name' => 'Demo household']);

        $this->addMember($household, 'admin@example.com', 'manager');
        $this->addMember($household, 'user@example.com', 'member');

        foreach (self::APPLIANCES as $applianceData) {
            $appliance = $household->appliances()->firstOrCreate(
                ['name' => $applianceData['name']],
                ['appliance_type' => $applianceData['appliance_type']],
            );

            foreach ($applianceData['shelves'] as $index => $shelfData) {
                $appliance->shelves()->firstOrCreate(
                    ['label' => $shelfData['label']],
                    ['shelf_order' => $index + 1, 'max_load_capacity_g' => $shelfData['max_load_capacity_g']],
                );
            }
        }
    }

    private function addMember(Household $household, string $email, string $role): void
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $household->members()->firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $role, 'joined_at' => now()],
        );
    }
}
