<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\ProductCatalog;
use App\Services\Authenticator;
use App\Services\CatalogCopier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Hides a product from the household's catalogue (soft delete). Food items already made from it keep
     * referring to it. A household still using the default tree gets its own copy first. Managers only.
     */
    public function destroy(Authenticator $authenticator, CatalogCopier $catalogCopier, string $id, string $productId): RedirectResponse
    {
        $household = Household::query()->findOrFail($id);

        abort_unless($household->isManagedBy($authenticator->user()->id), 403);

        $product = ProductCatalog::query()
            ->where('household_id', $household->catalogueOwnerId())
            ->findOrFail($productId);

        $categoryId = DB::transaction(function () use ($household, $catalogCopier, $product): string {
            $copiedProductIds = $catalogCopier->copyDefaultTo($household)['productIds'];
            $householdProduct = $household->products()->findOrFail($copiedProductIds[$product->id] ?? $product->id);

            $householdProduct->delete();

            return $householdProduct->category_id;
        });

        return redirect()->route('households.catalogue', $household->id)
            ->with('opened_category_id', $categoryId);
    }
}
