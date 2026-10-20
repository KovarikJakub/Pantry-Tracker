<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\ProductCatalog;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a demo household managed by admin@example.com, with user@example.com as a member,
 * two appliances, their shelves and food items on them. Depends on UserSeeder and CatalogSeeder.
 */
class HouseholdSeeder extends Seeder
{
    /**
     * Food item columns: product barcode, weight/volume, price, days until expiry (negative = expired), status.
     */
    private const APPLIANCES = [
        [
            'name' => 'Kitchen fridge',
            'appliance_type' => 'fridge',
            'shelves' => [
                ['label' => 'Top shelf', 'max_load_capacity_g' => 5000, 'items' => [
                    ['8590001000011', 500, 45.90, 6, 'stored'],
                    ['8590001000028', 150, 14.50, 1, 'stored'],
                    ['8590001000028', 150, 14.50, -2, 'stored'],
                    ['8590001000035', 200, 59.90, 20, 'stored'],
                    ['8590001000042', 1000, 24.90, 3, 'stored'],
                    ['8590001000042', 1000, 24.90, -5, 'consumed'],
                ]],
                ['label' => 'Middle shelf', 'max_load_capacity_g' => 8000, 'items' => [
                    ['8590001000059', 500, 89.90, 2, 'stored'],
                    ['8590001000073', 500, 79.90, -1, 'discarded'],
                    ['8590001000127', 1000, 39.90, 10, 'stored'],
                ]],
                ['label' => 'Vegetable drawer', 'max_load_capacity_g' => 6000, 'items' => [
                    ['8590001000080', 1000, 34.90, 14, 'stored'],
                    ['8590001000097', 1000, 32.90, 4, 'stored'],
                    ['8590001000103', 1000, 19.90, 21, 'stored'],
                ]],
            ],
        ],
        [
            'name' => 'Basement freezer',
            'appliance_type' => 'freezer',
            'shelves' => [
                ['label' => 'Upper drawer', 'max_load_capacity_g' => 10000, 'items' => [
                    ['8590001000066', 400, 69.90, 90, 'stored'],
                    ['8590001000110', 500, 42.90, 60, 'stored'],
                ]],
                ['label' => 'Lower drawer', 'max_load_capacity_g' => 15000, 'items' => [
                    ['8590001000059', 500, 89.90, 120, 'stored'],
                    ['8590001000073', 500, 79.90, 120, 'stored'],
                ]],
            ],
        ],
    ];

    public function run(): void
    {
        $household = Household::query()->firstOrCreate(['name' => 'Demo household']);

        $this->addMember($household, 'admin@example.com', 'manager');
        $member = $this->addMember($household, 'user@example.com', 'member');

        foreach (self::APPLIANCES as $applianceData) {
            $appliance = $household->appliances()->firstOrCreate(
                ['name' => $applianceData['name']],
                ['appliance_type' => $applianceData['appliance_type']],
            );

            foreach ($applianceData['shelves'] as $index => $shelfData) {
                $shelf = $appliance->shelves()->firstOrCreate(
                    ['label' => $shelfData['label']],
                    ['shelf_order' => $index + 1, 'max_load_capacity_g' => $shelfData['max_load_capacity_g']],
                );

                $this->addItems($shelf, $shelfData['items'], $member);
            }
        }
    }

    private function addMember(Household $household, string $email, string $role): User
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $household->members()->firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $role, 'joined_at' => now()],
        );

        return $user;
    }

    /**
     * Adds the items only to an empty shelf, so re-running does not duplicate them.
     *
     * @param  list<array{string, int, float, int, string}>  $items
     */
    private function addItems(Shelf $shelf, array $items, User $registeredBy): void
    {
        if ($shelf->foodItems()->exists()) {
            return;
        }

        foreach ($items as [$barcode, $weightVolume, $price, $daysUntilExpiry, $status]) {
            $product = ProductCatalog::query()->whereNull('household_id')->where('barcode', $barcode)->firstOrFail();

            $shelf->foodItems()->create([
                'product_id' => $product->id,
                'registered_by_user_id' => $registeredBy->id,
                'weight_volume' => $weightVolume,
                'purchase_price' => $price,
                'expiration_date' => now()->addDays($daysUntilExpiry),
                'status' => $status,
                'added_at' => now()->subDays(7),
                'removed_at' => $status === 'stored' ? null : now()->subDay(),
            ]);
        }
    }
}
