<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductCatalog;
use Illuminate\Database\Seeder;

/**
 * Seeds the default category tree and its global products (rows with a NULL household_id). Safe to re-run.
 */
class CatalogSeeder extends Seeder
{
    /**
     * String keys are subcategories. List entries are products in that category:
     * barcode, name, size, unit, default price, default expiry (days), kcal, protein, fat, carbs, fibre (per 100 g/ml).
     *
     * @var array<string, array<mixed>>
     */
    private const TREE = [
        'Dairy products' => [
            'Yoghurts' => [
                'Plain yoghurts' => [
                    ['8590001000011', 'Plain Greek yoghurt', 500, 'g', 45, 21, 97, 9.0, 5.0, 4.0, 0],
                ],
                'Fruit yoghurts' => [
                    ['8590001000028', 'Strawberry yoghurt', 150, 'g', 15, 14, 95, 3.5, 2.8, 13.5, 0.3],
                ],
            ],
            'Cheese' => [
                ['8590001000035', 'Cheddar', 200, 'g', 65, 30, 403, 25.0, 33.0, 1.3, 0],
                ['8590001000134', 'Mozzarella', 125, 'g', 30, 14, 254, 18.0, 20.0, 1.0, 0],
            ],
            'Milk' => [
                ['8590001000042', 'Semi-skimmed milk', 1000, 'ml', 25, 7, 47, 3.4, 1.5, 4.8, 0],
            ],
            'Butter' => [
                ['8590001000141', 'Butter', 250, 'g', 55, 60, 717, 0.9, 81.0, 0.1, 0],
            ],
            'Eggs' => [
                ['8590001000158', 'Free-range eggs (10 pcs)', 600, 'g', 60, 28, 143, 12.6, 9.5, 0.7, 0],
            ],
        ],
        'Meat' => [
            'Poultry' => [
                ['8590001000059', 'Chicken breast', 500, 'g', 90, 3, 110, 23.0, 1.5, 0, 0],
            ],
            'Pork' => [
                ['8590001000066', 'Pork chops', 400, 'g', 75, 3, 231, 19.0, 17.0, 0, 0],
                ['8590001000165', 'Sliced ham', 100, 'g', 35, 7, 145, 21.0, 6.0, 1.5, 0],
            ],
            'Beef' => [
                ['8590001000073', 'Minced beef', 500, 'g', 95, 2, 250, 17.0, 20.0, 0, 0],
            ],
        ],
        'Fish' => [
            ['8590001000172', 'Salmon fillet', 250, 'g', 120, 2, 208, 20.0, 13.0, 0, 0],
        ],
        'Frozen food' => [
            ['8590001000189', 'Frozen peas', 1000, 'g', 50, 365, 81, 5.4, 0.4, 14.0, 5.1],
            ['8590001000196', 'Vanilla ice cream', 900, 'ml', 85, 365, 207, 3.5, 11.0, 24.0, 0.7],
        ],
        'Fruit & vegetables' => [
            'Fruit' => [
                ['8590001000080', 'Apples', 1000, 'g', 40, 21, 52, 0.3, 0.2, 14.0, 2.4],
                ['8590001000097', 'Bananas', 1000, 'g', 35, 7, 89, 1.1, 0.3, 23.0, 2.6],
            ],
            'Vegetables' => [
                ['8590001000103', 'Carrots', 1000, 'g', 20, 21, 41, 0.9, 0.2, 10.0, 2.8],
                ['8590001000202', 'Tomatoes', 500, 'g', 45, 7, 18, 0.9, 0.2, 3.9, 1.2],
            ],
        ],
        'Bakery' => [
            ['8590001000110', 'Wholegrain bread', 500, 'g', 40, 5, 247, 13.0, 3.4, 41.0, 7.0],
        ],
        'Beverages' => [
            ['8590001000127', 'Orange juice', 1000, 'ml', 45, 14, 45, 0.7, 0.2, 10.4, 0.2],
        ],
    ];

    public function run(): void
    {
        $this->seedChildren(self::TREE, null);
    }

    /**
     * @param  array<string, array<mixed>>  $children
     */
    private function seedChildren(array $children, ?string $parentId): void
    {
        foreach ($children as $name => $entries) {
            $category = Category::query()->firstOrCreate([
                'household_id' => null,
                'parent_id' => $parentId,
                'name' => $name,
            ]);

            $this->seedChildren(array_filter($entries, 'is_string', ARRAY_FILTER_USE_KEY), $category->id);

            foreach (array_filter($entries, 'is_int', ARRAY_FILTER_USE_KEY) as $product) {
                $this->seedProduct($product, $category);
            }
        }
    }

    /**
     * @param  array{string, string, int, string, int, int, float, float, float, float, float}  $product
     */
    private function seedProduct(array $product, Category $category): void
    {
        [$barcode, $name, $size, $unit, $price, $expiryDays, $calories, $protein, $fat, $carbs, $fiber] = $product;

        ProductCatalog::query()->updateOrCreate(
            ['household_id' => null, 'barcode' => $barcode],
            [
                'category_id' => $category->id,
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
            ],
        );
    }
}
