<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\PlayAreaSession;
use App\Models\PlayAreaSessionItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PlayAreaBillingController extends Controller
{
    public function listActiveSessions()
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $sessions = PlayAreaSession::with(['package', 'items.product'])
            ->whereIn('status', ['active', 'pending'])
            ->orderByDesc('id')
            ->get()
            ->map(function (PlayAreaSession $session) {
                return [
                    'session' => $session,
                    'totals' => $this->calculateSessionTotals($session),
                ];
            })
            ->values();

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function createSession(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'employee_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id',
            'customer.name' => 'nullable|string|max:255',
            'customer.contactNumber' => 'nullable|string|max:50',
            'customer.email' => 'nullable|email|max:255',
            'customer.age' => 'nullable|integer|min:0|max:120',
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $package = $validated['package_id'] ? Package::findOrFail($validated['package_id']) : null;
        $customerAge = $request->input('customer.age');
        $additionalPayment = 0;
        $packageTotal = 0;
        $start = now();
        $expectedEnd = null;
        $open = $request->boolean('open', true);

        if ($package) {
            if (!is_null($customerAge) && $customerAge > $package->age_threshold && $package->additional_payment) {
                $additionalPayment = (float) $package->additional_payment;
            }
            $packageTotal = (float) $package->base_price + $additionalPayment;
            $expectedEnd = (clone $start)->addMinutes($package->base_time_minutes);
        }

        DB::beginTransaction();

        try {
            $temporaryBarcode = $open ? 'TMP' . now()->format('ymdHis') . random_int(1000, 9999) : null;

            $session = PlayAreaSession::create([
                'barcode' => $temporaryBarcode,
                'package_id' => $package?->id,
                'employee_id' => $request->input('employee_id'),
                'user_id' => $request->input('user_id'),
                'customer_name' => $request->input('customer.name'),
                'customer_contact' => $request->input('customer.contactNumber'),
                'customer_email' => $request->input('customer.email'),
                'customer_age' => $customerAge,
                'base_price' => $package?->base_price ?? 0,
                'base_time_minutes' => $package?->base_time_minutes ?? 0,
                'additional_payment' => $additionalPayment,
                'package_total' => $packageTotal,
                'extra_charge' => $package?->extra_charge ?? 0,
                'extra_charge_per_minutes' => max(1, (int) ($package?->extra_charge_per_minutes ?? 1)),
                'start_time' => $open ? $start : null,
                'expected_end_time' => $open ? $expectedEnd : null,
                'note' => $request->input('note'),
                'status' => $open ? 'active' : 'pending',
            ]);

            if ($open) {
                $session->update([
                    'barcode' => $this->generateEncryptedBarcode($session->id),
                ]);
            }

            $productsTotal = 0;

            foreach ($request->input('products', []) as $item) {
                $product = Product::find($item['id']);
                if (!$product) {
                    continue;
                }

                $qty = max(1, (int) $item['quantity']);
                $lineTotal = $qty * (float) $product->selling_price;

                PlayAreaSessionItem::create([
                    'play_area_session_id' => $session->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'total_price' => $lineTotal,
                ]);

                $productsTotal += $lineTotal;
            }

            $session->update([
                'products_total' => $productsTotal,
                'final_total' => $packageTotal + $productsTotal,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Play area session created successfully.',
                'session' => $session->fresh(['package', 'items.product']),
                'barcode_print_url' => $open ? route('play-area.barcode.print', $session->id) : null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create play area session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function openSession(Request $request, $id)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $session = PlayAreaSession::with(['package', 'items.product'])->findOrFail($id);

        if ($session->status !== 'pending') {
            return response()->json(['message' => 'Session is not in pending state.'], 422);
        }

        $start = now();
        $expectedEnd = $session->package_id
            ? (clone $start)->addMinutes((int) $session->base_time_minutes)
            : null;

        $temporaryBarcode = 'TMP' . $start->format('ymdHis') . random_int(1000, 9999);

        $session->update([
            'barcode' => $temporaryBarcode,
            'start_time' => $start,
            'expected_end_time' => $expectedEnd,
            'status' => 'active',
        ]);

        $session->update(['barcode' => $this->generateEncryptedBarcode($session->id)]);
        $session->refresh()->load(['package', 'items.product']);

        return response()->json([
            'message' => 'Session opened successfully.',
            'session' => $session,
            'barcode_print_url' => route('play-area.barcode.print', $session->id),
        ]);
    }

    public function getSessionByBarcode(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'barcode' => 'required|string',
        ]);

        $session = PlayAreaSession::with(['package', 'items.product'])
            ->where('barcode', $request->input('barcode'))
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'Session not found for this barcode.',
            ], 404);
        }

        $totals = $this->calculateSessionTotals($session);

        return response()->json([
            'session' => $session,
            'totals' => $totals,
        ]);
    }

    public function closeSession(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'barcode' => 'required|string',
            'payment_method' => 'required|in:cash,card',
            'cash' => 'nullable|numeric|min:0',
            'employee_id' => 'nullable|exists:employees,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $session = PlayAreaSession::with(['items.product', 'package'])
            ->where('barcode', $validated['barcode'])
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'Session not found for this barcode.',
            ], 404);
        }

        if ($session->status === 'closed') {
            return response()->json([
                'message' => 'This session is already closed.',
                'session' => $session,
            ], 422);
        }

        $totals = $this->calculateSessionTotals($session);
        $cash = (float) ($validated['cash'] ?? 0);

        if ($validated['payment_method'] === 'cash' && $cash < $totals['final_total']) {
            return response()->json([
                'message' => 'Cash is not enough for the final amount.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($session->items as $item) {
                $product = $item->product;

                if (!$product) {
                    continue;
                }

                $newStock = $product->stock_quantity - $item->quantity;

                if ($newStock < 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Insufficient stock for product: {$product->name}",
                    ], 423);
                }

                $product->update([
                    'stock_quantity' => $newStock,
                ]);

                StockTransaction::create([
                    'product_id' => $product->id,
                    'transaction_type' => 'Sold',
                    'quantity' => $item->quantity,
                    'transaction_date' => now(),
                    'supplier_id' => $product->supplier_id ?? null,
                ]);
            }

            $customer = null;

            if ($session->customer_name || $session->customer_email || $session->customer_contact) {
                if ($session->customer_email) {
                    $customer = Customer::where('email', $session->customer_email)->first();
                }

                if (!$customer && $session->customer_contact) {
                    $customer = Customer::where('phone', $session->customer_contact)->first();
                }

                if (!$customer) {
                    $customer = Customer::create([
                        'name' => $session->customer_name,
                        'email' => $session->customer_email,
                        'phone' => $session->customer_contact,
                        'address' => '',
                        'member_since' => now()->toDateString(),
                        'loyalty_points' => 0,
                    ]);
                }
            }

            $sale = Sale::create([
                'customer_id' => $customer?->id,
                'employee_id' => $validated['employee_id'] ?? $session->employee_id,
                'user_id' => $validated['user_id'] ?? $session->user_id,
                'order_id' => $session->barcode,
                'total_amount' => $totals['final_total'],
                'discount' => 0,
                'total_cost' => 0,
                'payment_method' => $validated['payment_method'],
                'sale_date' => now()->toDateString(),
                'cash' => $cash,
                'custom_discount' => 0,
            ]);

            foreach ($session->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            $session->update([
                'end_time' => now(),
                'extra_minutes' => $totals['extra_minutes'],
                'extra_amount' => $totals['extra_amount'],
                'products_total' => $totals['products_total'],
                'final_total' => $totals['final_total'],
                'payment_method' => $validated['payment_method'],
                'cash' => $cash,
                'status' => 'closed',
            ]);

            DB::commit();

            $totals['cash'] = $cash;

            return response()->json([
                'message' => 'Play area session closed successfully.',
                'session' => $session->fresh(['package', 'items.product']),
                'totals' => $totals,
                'balance' => round($cash - $totals['final_total'], 2),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to close session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncSessionItems(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if (!$request->filled('barcode') && !$request->filled('session_id')) {
            return response()->json(['message' => 'session_id or barcode is required.'], 422);
        }

        $session = $request->filled('barcode')
            ? PlayAreaSession::with(['items.product', 'package'])->where('barcode', $request->input('barcode'))->first()
            : PlayAreaSession::with(['items.product', 'package'])->find($request->input('session_id'));

        if (!$session) {
            return response()->json([
                'message' => 'Session not found.',
            ], 404);
        }

        if ($session->status === 'closed') {
            return response()->json([
                'message' => 'Cannot update products for a closed session.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            PlayAreaSessionItem::where('play_area_session_id', $session->id)->delete();

            $productsTotal = 0;

            foreach ($request->input('products', []) as $item) {
                $product = Product::find($item['id']);

                if (!$product) {
                    continue;
                }

                $qty = max(1, (int) $item['quantity']);
                $lineTotal = $qty * (float) $product->selling_price;

                PlayAreaSessionItem::create([
                    'play_area_session_id' => $session->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'total_price' => $lineTotal,
                ]);

                $productsTotal += $lineTotal;
            }

            $session->update([
                'products_total' => $productsTotal,
                'final_total' => (float) $session->package_total + $productsTotal,
            ]);

            DB::commit();

            $session->refresh()->load(['package', 'items.product']);
            $totals = $this->calculateSessionTotals($session);

            return response()->json([
                'message' => 'Session items updated successfully.',
                'session' => $session,
                'totals' => $totals,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update session items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function printBarcode($id)
    {
        $session = PlayAreaSession::with('package')->findOrFail($id);
        $company = \App\Models\CompanyInfo::first();

        return view('barcode-play-area', [
            'session' => $session,
            'company' => $company,
        ]);
    }

    private function calculateSessionTotals(PlayAreaSession $session): array
    {
        $now = now();
        $productsTotal = (float) $session->items->sum('total_price');

        // Pending session — no start time yet
        if (!$session->start_time) {
            return [
                'elapsed_minutes' => 0,
                'extra_minutes' => 0,
                'extra_amount' => 0.0,
                'products_total' => round($productsTotal, 2),
                'package_total' => round((float) $session->package_total, 2),
                'final_total' => round((float) $session->package_total + $productsTotal, 2),
                'expected_end_time' => null,
                'start_time' => null,
                'current_time' => $now->format('Y-m-d H:i:s'),
            ];
        }

        $start = Carbon::parse($session->start_time);

        // No package — pure product order, no time tracking
        if (!$session->package_id) {
            return [
                'elapsed_minutes' => 0,
                'extra_minutes' => 0,
                'extra_amount' => 0.0,
                'products_total' => round($productsTotal, 2),
                'package_total' => 0.0,
                'final_total' => round($productsTotal, 2),
                'expected_end_time' => null,
                'start_time' => $start->format('Y-m-d H:i:s'),
                'current_time' => $now->format('Y-m-d H:i:s'),
            ];
        }

        $elapsedMinutes = max(0, $start->diffInMinutes($now));
        $extraMinutes = max(0, $elapsedMinutes - (int) $session->base_time_minutes);
        $extraUnits = $extraMinutes > 0
            ? (int) ceil($extraMinutes / max(1, (int) $session->extra_charge_per_minutes))
            : 0;

        $extraAmount = $extraUnits * (float) $session->extra_charge;
        $finalTotal = (float) $session->package_total + $extraAmount + $productsTotal;

        return [
            'elapsed_minutes' => $elapsedMinutes,
            'extra_minutes' => $extraMinutes,
            'extra_amount' => round($extraAmount, 2),
            'products_total' => round($productsTotal, 2),
            'package_total' => round((float) $session->package_total, 2),
            'final_total' => round($finalTotal, 2),
            'expected_end_time' => $session->expected_end_time ? Carbon::parse($session->expected_end_time)->format('Y-m-d H:i:s') : null,
            'start_time' => $start->format('Y-m-d H:i:s'),
            'current_time' => $now->format('Y-m-d H:i:s'),
        ];
    }

    private function generateEncryptedBarcode(int $sessionId): string
    {
        $hash = strtoupper(substr(hash_hmac('sha256', 'ORDER-' . $sessionId, config('app.key', 'play-area-pos')), 0, 12));
        $barcode = 'PA' . $hash;

        $suffix = 1;
        while (PlayAreaSession::where('barcode', $barcode)->where('id', '!=', $sessionId)->exists()) {
            $barcode = 'PA' . $hash . $suffix;
            $suffix++;
        }

        return $barcode;
    }
}
