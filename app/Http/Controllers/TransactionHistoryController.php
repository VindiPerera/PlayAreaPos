<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product; 
use App\Models\CompanyInfo;
use App\Models\StockTransaction;
use App\Models\PlayAreaSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class TransactionHistoryController extends Controller
{
    public function index()
{
    $allhistoryTransactions = Sale::with([
            'saleItems',
            'saleItems.product',
            'customer',
            'user',
            'playAreaSession',
            'playAreaSession.package',
            'playAreaSession.items',
            'playAreaSession.items.product',
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    $companyInfo = CompanyInfo::all();

    return Inertia::render('TransactionHistory/Index', [
        'allhistoryTransactions' => $allhistoryTransactions,
        'totalhistoryTransactions' => $allhistoryTransactions->count(),
        'companyInfo' => $companyInfo,
    ]);
}

public function destroy(Request $request)
{
    $request->validate([
        'order_id' => 'required|string|exists:sales,order_id',
    ]);

    $sale = Sale::where('order_id', $request->order_id)->first();

    if (!$sale) {
        return response()->json(['message' => 'Sale not found'], 404);
    }

    // Retrieve sale items and restore product stock
    foreach ($sale->saleItems as $saleItem) {
        $product = Product::find($saleItem->product_id);
        if ($product) {
            $product->increment('stock_quantity', $saleItem->quantity);
            
            // Log the stock transaction
            StockTransaction::create([
                'product_id' => $saleItem->product_id,
                'transaction_type' => 'Deleted',
                'quantity' => $saleItem->quantity,
                'transaction_date' => now(),
                'supplier_id' => $product->supplier_id ?? null, // If applicable
                'reason' => null,
            ]);
        }
    }

    // Delete associated sale items
    SaleItem::where('sale_id', $sale->id)->delete();

    // Delete the sale record
    $sale->delete();

    return redirect()->route('transactionHistory.index')->banner('Record deleted successfully, and stock updated.');
}

public function cancel(Request $request)
{
    $request->validate([
        'order_id'     => 'required|string|exists:sales,order_id',
        'cancel_reason' => 'nullable|string|max:500',
    ]);

    $sale = Sale::where('order_id', $request->order_id)->first();

    if (!$sale) {
        return redirect()->back()->with('error', 'Sale not found.');
    }

    if ($sale->status === 'cancelled') {
        return redirect()->back()->with('error', 'This bill has already been cancelled.');
    }

    DB::transaction(function () use ($sale, $request) {
        // Restore stock for every sold item
        foreach ($sale->saleItems as $saleItem) {
            $product = Product::find($saleItem->product_id);
            if ($product) {
                $product->increment('stock_quantity', $saleItem->quantity);

                StockTransaction::create([
                    'product_id'       => $saleItem->product_id,
                    'transaction_type' => 'Cancelled',
                    'quantity'         => $saleItem->quantity,
                    'transaction_date' => now(),
                    'supplier_id'      => $product->supplier_id ?? null,
                    'reason'           => 'Bill cancelled: ' . $sale->order_id,
                ]);
            }
        }

        // Soft-cancel the sale (keeps the record for audit trail)
        $sale->update([
            'status'       => 'cancelled',
            'cancel_reason' => $request->cancel_reason ?? null,
            'cancelled_at' => now(),
        ]);
    });

    return redirect()->route('transactionHistory.index')->banner('Bill ' . $sale->order_id . ' has been cancelled and stock restored.');
}

public function reprintData($order_id)
{
    $sale = Sale::with(['saleItems.product', 'customer'])
        ->where('order_id', $order_id)
        ->firstOrFail();

    $session = PlayAreaSession::with(['package', 'items.product'])
        ->where('barcode', $order_id)
        ->first();

    $totals  = null;
    $balance = 0;

    if ($session) {
        $elapsed = 0;
        if ($session->start_time && $session->end_time) {
            $elapsed = (int) Carbon::parse($session->start_time)
                ->diffInMinutes(Carbon::parse($session->end_time));
        }

        $totals = [
            'elapsed_minutes' => $elapsed,
            'extra_minutes'   => (int)   ($session->extra_minutes  ?? 0),
            'extra_amount'    => (float)  ($session->extra_amount   ?? 0),
            'products_total'  => (float)  ($session->products_total ?? 0),
            'package_total'   => (float)  ($session->package_total  ?? 0),
            'final_total'     => (float)  ($session->final_total    ?? $sale->total_amount),
            'cash'            => (float)  ($session->cash           ?? $sale->cash ?? 0),
        ];

        $balance = round($totals['cash'] - $totals['final_total'], 2);
    }

    return response()->json([
        'session' => $session,
        'totals'  => $totals,
        'balance' => $balance,
        'sale'    => $sale,
    ]);
}


}
