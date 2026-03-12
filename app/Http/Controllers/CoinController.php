<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameCoinEntry;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CoinController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $selectedDate = $request->input('date', now()->toDateString());

        $coinCategoryIds = Category::query()
            ->whereRaw('LOWER(name) IN (?, ?)', ['coin', 'coins'])
            ->pluck('id');

        $coinProducts = Product::query()
            ->when($coinCategoryIds->isNotEmpty(), fn ($q) => $q->whereIn('category_id', $coinCategoryIds), fn ($q) => $q->whereRaw('1=0'))
            ->select(['id', 'name', 'selling_price', 'barcode', 'category_id'])
            ->orderBy('name')
            ->get();

        $games = Game::with('coinProduct:id,name,selling_price,barcode')
            ->orderBy('name')
            ->get();

        $entries = GameCoinEntry::with(['game:id,name,coin_product_id', 'game.coinProduct:id,name,selling_price'])
            ->whereDate('entry_date', Carbon::parse($selectedDate)->toDateString())
            ->get();

        $entriesByGame = $entries->keyBy('game_id')->map(fn ($entry) => [
            'id' => $entry->id,
            'game_id' => $entry->game_id,
            'coin_count' => (int) $entry->coin_count,
            'coin_price' => (float) $entry->coin_price,
            'total_amount' => (float) $entry->total_amount,
            'entry_date' => optional($entry->entry_date)->format('Y-m-d'),
        ])->values();

        return Inertia::render('Coins/Index', [
            'selectedDate' => $selectedDate,
            'games' => $games,
            'coinProducts' => $coinProducts,
            'entries' => $entriesByGame,
            'dailySummary' => [
                'total_coin_count' => (int) $entries->sum('coin_count'),
                'total_amount' => (float) $entries->sum('total_amount'),
            ],
        ]);
    }

    public function storeGame(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:games,name',
            'coin_product_id' => 'required|exists:products,id',
        ]);

        $this->ensureCoinProduct($validated['coin_product_id']);

        $game = Game::create([
            'name' => $validated['name'],
            'coin_product_id' => $validated['coin_product_id'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Game created successfully.',
            'game' => $game->load('coinProduct:id,name,selling_price,barcode'),
        ]);
    }

    public function upsertDailyCount(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'entry_date' => 'required|date',
            'coin_count' => 'required|integer|min:0',
        ]);

        $game = Game::with('coinProduct')->findOrFail($validated['game_id']);

        if (!$game->coinProduct) {
            return response()->json([
                'message' => 'This game has no assigned coin product.',
            ], 422);
        }

        $this->ensureCoinProduct($game->coin_product_id);

        $coinPrice = (float) $game->coinProduct->selling_price;
        $coinCount = (int) $validated['coin_count'];
        $totalAmount = $coinCount * $coinPrice;

        $entry = GameCoinEntry::updateOrCreate(
            [
                'game_id' => $game->id,
                'entry_date' => Carbon::parse($validated['entry_date'])->toDateString(),
            ],
            [
                'user_id' => auth()->id(),
                'coin_count' => $coinCount,
                'coin_price' => $coinPrice,
                'total_amount' => $totalAmount,
            ]
        );

        return response()->json([
            'message' => 'Daily coin count saved successfully.',
            'entry' => [
                'id' => $entry->id,
                'game_id' => $entry->game_id,
                'coin_count' => (int) $entry->coin_count,
                'coin_price' => (float) $entry->coin_price,
                'total_amount' => (float) $entry->total_amount,
                'entry_date' => Carbon::parse($entry->entry_date)->format('Y-m-d'),
            ],
        ]);
    }

    public function dailyReport(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $selectedDate = Carbon::parse($request->input('date', now()->toDateString()))->toDateString();

        $entries = GameCoinEntry::with(['game:id,name', 'game.coinProduct:id,name'])
            ->whereDate('entry_date', $selectedDate)
            ->orderBy('game_id')
            ->get();

        return response()->json([
            'date' => $selectedDate,
            'rows' => $entries->map(fn ($entry) => [
                'game_name' => $entry->game?->name,
                'coin_product_name' => $entry->game?->coinProduct?->name,
                'coin_price' => (float) $entry->coin_price,
                'coin_count' => (int) $entry->coin_count,
                'total_amount' => (float) $entry->total_amount,
            ]),
            'summary' => [
                'total_coin_count' => (int) $entries->sum('coin_count'),
                'total_amount' => (float) $entries->sum('total_amount'),
            ],
        ]);
    }

    private function ensureCoinProduct(int $productId): void
    {
        $coinCategoryIds = Category::query()
            ->whereRaw('LOWER(name) IN (?, ?)', ['coin', 'coins'])
            ->pluck('id');

        $isCoinProduct = Product::query()
            ->where('id', $productId)
            ->whereIn('category_id', $coinCategoryIds)
            ->exists();

        if (!$isCoinProduct) {
            abort(422, 'Selected product is not a Coins category product.');
        }
    }
}
