<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryTree
{
    /**
     * @param  Collection<string, Category>  $categoriesById
     * @return list<string> the category's id followed by the ids of all its ancestors; empty if not found
     */
    public static function ancestorIds(Collection $categoriesById, ?string $categoryId): array
    {
        $ids = [];
        $category = $categoryId === null ? null : $categoriesById->get($categoryId);

        while ($category !== null) {
            $ids[] = $category->id;
            $category = $category->parent_id === null ? null : $categoriesById->get($category->parent_id);
        }

        return $ids;
    }
}
