<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display packages listing page
     */
    public function index()
    {
        $packages = Package::all();
        return Inertia::render('Packages/Index', ['packages' => $packages]);
    }

    /**
     * Show the form for creating a new package
     */
    public function create()
    {
        return Inertia::render('Packages/Create');
    }

    /**
     * Store a newly created package in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:elder,kid',
            'base_price' => 'required|numeric|min:0',
            'base_time_minutes' => 'required|integer|min:1',
            'extra_charge' => 'required|numeric|min:0',
            'extra_charge_per_minutes' => 'required|integer|min:1',
            'age_threshold' => 'required|integer|min:0',
            'additional_payment' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Package::create($validated);

        return redirect()->route('packages.index')
                       ->with('success', 'Package created successfully!');
    }

    /**
     * Display the specified package
     */
    public function show(Package $package)
    {
        return Inertia::render('Packages/Show', ['package' => $package]);
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit(Package $package)
    {
        return Inertia::render('Packages/Edit', ['package' => $package]);
    }

    /**
     * Update the specified package in database
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:elder,kid',
            'base_price' => 'required|numeric|min:0',
            'base_time_minutes' => 'required|integer|min:1',
            'extra_charge' => 'required|numeric|min:0',
            'extra_charge_per_minutes' => 'required|integer|min:1',
            'age_threshold' => 'required|integer|min:0',
            'additional_payment' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $package->update($validated);

        return redirect()->route('packages.index')
                       ->with('success', 'Package updated successfully!');
    }

    /**
     * Delete the specified package
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('packages.index')
                       ->with('success', 'Package deleted successfully!');
    }

    /**
     * Calculate price for a package
     */
    public function calculatePrice(Request $request, Package $package)
    {
        $validated = $request->validate([
            'duration_minutes' => 'required|integer|min:1',
            'age' => 'nullable|integer|min:0',
        ]);

        $totalPrice = $package->calculatePrice(
            $validated['duration_minutes'],
            $validated['age'] ?? null
        );

        return response()->json([
            'total_price' => $totalPrice,
            'base_price' => $package->base_price,
            'base_time_minutes' => $package->base_time_minutes,
            'extra_charge' => $package->extra_charge,
            'extra_charge_per_minutes' => $package->extra_charge_per_minutes,
            'additional_payment' => $package->additional_payment,
        ]);
    }
}
