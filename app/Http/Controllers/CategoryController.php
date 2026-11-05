<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Services\Authenticator;
use App\Services\CatalogCopier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Adds a subcategory under one of the household's categories. Managers only.
     * A household still using the default tree gets its own copy first, so the default tree is never changed.
     */
    public function store(Request $request, Authenticator $authenticator, CatalogCopier $catalogCopier, string $id): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $validated = $request->validateWithBag('category', [
            'parent_id' => ['required', 'uuid'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where('parent_id', (string) $request->input('parent_id')),
            ],
        ], [
            'name.unique' => 'This category already has a subcategory with that name.',
        ]);

        $parent = Category::query()
            ->where('household_id', $household->catalogueOwnerId())
            ->findOrFail($validated['parent_id']);

        $parentId = DB::transaction(function () use ($household, $catalogCopier, $parent, $validated): string {
            $copiedCategoryIds = $catalogCopier->copyDefaultTo($household)['categoryIds'];
            $parentId = $copiedCategoryIds[$parent->id] ?? $parent->id;

            $household->categories()->create([
                'name' => $validated['name'],
                'parent_id' => $parentId,
            ]);

            return $parentId;
        });

        return redirect()->route('households.catalogue', $household->id)
            ->with('opened_category_id', $parentId);
    }
}
