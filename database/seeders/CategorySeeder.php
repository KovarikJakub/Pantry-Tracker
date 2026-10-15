<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeds the default category tree (rows with a NULL household_id). Safe to re-run.
 */
class CategorySeeder extends Seeder
{
    /**
     * Category name => subcategories.
     *
     * @var array<string, array<mixed>>
     */
    private const TREE = [
        'Dairy products' => [
            'Yoghurts' => [
                'Plain yoghurts' => [],
                'Fruit yoghurts' => [],
            ],
            'Cheese' => [],
            'Milk' => [],
        ],
        'Meat' => [
            'Poultry' => [],
            'Pork' => [],
            'Beef' => [],
        ],
        'Fruit & vegetables' => [
            'Fruit' => [],
            'Vegetables' => [],
        ],
        'Bakery' => [],
        'Beverages' => [],
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
        foreach ($children as $name => $grandchildren) {
            $category = Category::query()->firstOrCreate([
                'household_id' => null,
                'parent_id' => $parentId,
                'name' => $name,
            ]);

            $this->seedChildren($grandchildren, $category->id);
        }
    }
}
