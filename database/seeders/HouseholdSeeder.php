<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\ProductCatalog;
use App\Models\Shelf;
use App\Models\User;
use App\Services\CatalogCopier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeds two demo households with members, appliances, shelves, stored food and three weeks of consumption
 * history. All dates are relative to today, so the demo never goes stale. Depends on UserSeeder and
 * CatalogSeeder. Meant for `migrate:fresh --seed`; re-running skips households that already exist.
 */
class HouseholdSeeder extends Seeder
{
    private const HISTORY_ITEMS = 20;

    private const HISTORY_DAYS = 21;

    /**
     * "members": email => role. "custom_catalogue": gets its own catalogue copy plus "custom_products".
     * Stored item columns: product barcode, weight/volume, days until expiry (negative = expired).
     * "history": barcodes cycled through for the consumed/discarded items.
     */
    private const HOUSEHOLDS = [
        [
            'name' => 'Novák family',
            'members' => [
                'owner@example.com' => HouseholdMember::ROLE_OWNER,
                'manager@example.com' => HouseholdMember::ROLE_MANAGER,
                'user@example.com' => HouseholdMember::ROLE_MEMBER,
            ],
            'custom_catalogue' => true,
            'custom_products' => [
                // category name, product row in CatalogSeeder's format
                'Fruit' => ['8590001000219', "Grandma's plum jam", 400, 'g', 0, 180, 250, 0.4, 0.1, 60.0, 1.0],
            ],
            'appliances' => [
                [
                    'name' => 'Kitchen fridge',
                    'appliance_type' => 'fridge',
                    'shelves' => [
                        // 4 700 / 5 000 g: nearly full
                        ['label' => 'Top shelf', 'max_load_capacity_g' => 5000, 'items' => [
                            ['8590001000042', 1000, 3],
                            ['8590001000042', 1000, 6],
                            ['8590001000127', 1000, 10],
                            ['8590001000011', 500, 1],
                            ['8590001000028', 150, -2],
                            ['8590001000035', 200, 20],
                            ['8590001000141', 250, 40],
                            ['8590001000158', 600, 12],
                        ]],
                        // 1 875 / 3 000 g
                        ['label' => 'Middle shelf', 'max_load_capacity_g' => 3000, 'items' => [
                            ['8590001000059', 500, 2],
                            ['8590001000073', 500, 2],
                            ['8590001000165', 100, -1],
                            ['8590001000134', 125, 9],
                            ['8590001000172', 250, 1],
                            ['8590001000219', 400, 60],
                        ]],
                        // 1 500 / 6 000 g
                        ['label' => 'Vegetable drawer', 'max_load_capacity_g' => 6000, 'items' => [
                            ['8590001000080', 1000, 14],
                            ['8590001000202', 500, 5],
                        ]],
                    ],
                ],
                [
                    'name' => 'Basement freezer',
                    'appliance_type' => 'freezer',
                    'shelves' => [
                        ['label' => 'Upper drawer', 'max_load_capacity_g' => 10000, 'items' => [
                            ['8590001000189', 1000, 200],
                            ['8590001000196', 900, 150],
                            ['8590001000066', 400, 90],
                        ]],
                        ['label' => 'Lower drawer', 'max_load_capacity_g' => 15000, 'items' => [
                            ['8590001000059', 500, 120],
                            ['8590001000073', 500, 120],
                        ]],
                    ],
                ],
            ],
            'history' => [
                '8590001000042', '8590001000011', '8590001000110', '8590001000097', '8590001000059',
                '8590001000028', '8590001000080', '8590001000035', '8590001000127', '8590001000165',
            ],
        ],
        [
            'name' => 'Shared flat',
            'members' => [
                'user@example.com' => HouseholdMember::ROLE_OWNER,
                'manager@example.com' => HouseholdMember::ROLE_MEMBER,
            ],
            'custom_catalogue' => false,
            'custom_products' => [],
            'appliances' => [
                [
                    'name' => 'Fridge',
                    'appliance_type' => 'fridge',
                    'shelves' => [
                        ['label' => 'Door', 'max_load_capacity_g' => 3000, 'items' => [
                            ['8590001000127', 1000, 8],
                            ['8590001000042', 1000, 4],
                        ]],
                        ['label' => 'Main shelf', 'max_load_capacity_g' => 4000, 'items' => [
                            ['8590001000134', 125, 3],
                            ['8590001000202', 500, 5],
                            ['8590001000165', 100, -1],
                        ]],
                    ],
                ],
            ],
            'history' => [
                '8590001000110', '8590001000158', '8590001000097', '8590001000134', '8590001000202',
                '8590001000042',
            ],
        ],
    ];

    public function run(CatalogCopier $catalogCopier): void
    {
        foreach (self::HOUSEHOLDS as $data) {
            if (Household::query()->where('name', $data['name'])->exists()) {
                continue;
            }

            $household = Household::query()->create(['name' => $data['name']]);

            $members = $this->addMembers($household, $data['members']);

            if ($data['custom_catalogue']) {
                $catalogCopier->copyDefaultTo($household);
                $household->refresh();
                $this->addCustomProducts($household, $data['custom_products']);
            }

            $shelves = $this->addAppliances($household, $data['appliances'], $members);

            $this->addHistory($household, $shelves, $members, $data['history']);
        }
    }

    /**
     * @param  array<string, string>  $roles  email => role
     * @return Collection<int, User>
     */
    private function addMembers(Household $household, array $roles): Collection
    {
        return collect($roles)->map(function (string $role, string $email) use ($household): User {
            $user = User::query()->where('email', $email)->firstOrFail();

            $household->members()->create([
                'user_id' => $user->id,
                'role' => $role,
                'joined_at' => now()->subMonths(2),
            ]);

            return $user;
        })->values();
    }

    /**
     * Adds household-only products to categories of the household's own catalogue copy.
     *
     * @param  array<string, array{string, string, int, string, int, int, float, float, float, float, float}>  $products
     */
    private function addCustomProducts(Household $household, array $products): void
    {
        foreach ($products as $categoryName => [$barcode, $name, $size, $unit, $price, $expiryDays, $calories, $protein, $fat, $carbs, $fiber]) {
            $household->products()->create([
                'category_id' => $household->categories()->where('name', $categoryName)->firstOrFail()->id,
                'barcode' => $barcode,
                'name' => $name,
                'default_size_g_ml' => $size,
                'measurement_unit' => $unit,
                'default_price' => $price,
                'default_expiry_days' => $expiryDays,
                'calories_per_100g' => $calories,
                'protein_per_100g' => $protein,
                'fat_per_100g' => $fat,
                'carbs_per_100g' => $carbs,
                'fiber_per_100g' => $fiber,
            ]);
        }
    }

    /**
     * Creates the appliances, shelves and stored food; members take turns registering the food.
     *
     * @param  list<array<string, mixed>>  $appliances
     * @param  Collection<int, User>  $members
     * @return Collection<int, Shelf>
     */
    private function addAppliances(Household $household, array $appliances, Collection $members): Collection
    {
        $shelves = collect();
        $itemIndex = 0;

        foreach ($appliances as $applianceData) {
            $appliance = $household->appliances()->create([
                'name' => $applianceData['name'],
                'appliance_type' => $applianceData['appliance_type'],
            ]);

            foreach ($applianceData['shelves'] as $order => $shelfData) {
                $shelf = $appliance->shelves()->create([
                    'label' => $shelfData['label'],
                    'shelf_order' => $order + 1,
                    'max_load_capacity_g' => $shelfData['max_load_capacity_g'],
                ]);
                $shelves->push($shelf);

                foreach ($shelfData['items'] as [$barcode, $weightVolume, $daysUntilExpiry]) {
                    $product = $this->product($household, $barcode);

                    $shelf->foodItems()->create([
                        'product_id' => $product->id,
                        'registered_by_user_id' => $members[$itemIndex % $members->count()]->id,
                        'weight_volume' => $weightVolume,
                        'purchase_price' => $this->price($product, $weightVolume),
                        'expiration_date' => today()->addDays($daysUntilExpiry),
                        'status' => 'stored',
                        'added_at' => now()->subDays(1 + $itemIndex % 5),
                    ]);

                    $itemIndex++;
                }
            }
        }

        return $shelves;
    }

    /**
     * Consumed and discarded food spread evenly over the last weeks: every fifth item is discarded, members take
     * turns resolving it. Deterministic, so the consumption profile looks the same after every reseed.
     *
     * @param  Collection<int, Shelf>  $shelves
     * @param  Collection<int, User>  $members
     * @param  list<string>  $barcodes
     */
    private function addHistory(Household $household, Collection $shelves, Collection $members, array $barcodes): void
    {
        for ($index = 0; $index < self::HISTORY_ITEMS; $index++) {
            $product = $this->product($household, $barcodes[$index % count($barcodes)]);
            $member = $members[$index % $members->count()];
            $removedAt = now()->subDays(intdiv($index * self::HISTORY_DAYS, self::HISTORY_ITEMS))->setTime(8 + $index % 12, 0);

            if ($removedAt->isFuture()) {
                $removedAt = now();
            }

            $addedAt = $removedAt->copy()->subDays(2 + $index % 4);

            $shelves[$index % $shelves->count()]->foodItems()->create([
                'product_id' => $product->id,
                'registered_by_user_id' => $member->id,
                'weight_volume' => $product->default_size_g_ml,
                'purchase_price' => $product->default_price,
                'expiration_date' => $addedAt->copy()->addDays($product->default_expiry_days)->toDateString(),
                'status' => $index % 5 === 4 ? 'thrown_out' : 'consumed',
                'added_at' => $addedAt,
                'removed_at' => $removedAt,
                'resolved_by_user_id' => $member->id,
            ]);
        }
    }

    /**
     * A product of the catalogue the household uses (its own copy or the default one).
     */
    private function product(Household $household, string $barcode): ProductCatalog
    {
        return $household->catalogueProducts()->where('barcode', $barcode)->firstOrFail();
    }

    /**
     * The product's default price, scaled to the stored amount.
     */
    private function price(ProductCatalog $product, int $weightVolume): float
    {
        return round((float) $product->default_price * $weightVolume / (float) $product->default_size_g_ml, 2);
    }
}
