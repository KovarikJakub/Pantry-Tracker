<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Services\Authenticator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ConsumptionController extends Controller {
    public function __construct(private Authenticator $authenticator) {}

    public function index(Request $request): View
    {
        $user = $this->authenticator->user();

        # reads ?period from the URL. If nothing is in the URL, it defaults to "day".
        $period = $request->string('period', 'day')->toString();

        [$from, $to] = match ($period) {
            'week' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'custom' => [
                Carbon::parse($request->input('from', now()->toDateString()))->startOfDay(),
                Carbon::parse($request->input('to', now()->toDateString()))->endOfDay(),

            ],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        $items = FoodItem::query()
            ->with('product')
            ->where('status', 'consumed')
            ->where('resolved_by_user_id', $user->id)
            ->whereBetween('removed_at', [$from, $to])
            ->orderByDesc('removed_at')
            ->get();

        $nutrition = [
            'calories' => 0.0,
            'protein' => 0.0,
            'fat' => 0.0,
            'carbs' => 0.0,
            'fiber' => 0.0,
            'price' => 0.0,
        ];

        foreach ($items as $item) {
            $factor = ($item->weight_volume ?? 0) / 100;
            $product = $item->product;

            $nutrition['calories'] += ($product->calories_per_100g ?? 0) * $factor;
            $nutrition['protein'] += ($product->protein_per_100g ?? 0) * $factor;
            $nutrition['fat'] += ($product->fat_per_100g ?? 0) * $factor;
            $nutrition['carbs'] += ($product->carbs_per_100g ?? 0) * $factor;
            $nutrition['fiber'] += ($product->fiber_per_100g ?? 0) * $factor;
            $nutrition['price'] += $item->purchase_price ?? 0;
        }

        return view('consumption.index', [
            'items' => $items,
            'nutrition' => $nutrition,
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }
}
