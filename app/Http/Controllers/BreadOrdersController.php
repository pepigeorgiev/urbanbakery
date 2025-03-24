<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BreadType;
use App\Models\BreadOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BreadOrdersController extends Controller
{
    public function index()
    {
        // Get selected date (default to tomorrow)
        $selectedDate = request('date', now()->addDay()->format('Y-m-d'));
        $defaultDate = $selectedDate;
        
        // Get all active bread types
        $breadTypes = BreadType::where('is_active', true)
                               ->orderBy('name')
                               ->get();
        
        // Get the user's orders for the selected date
        $userOrders = null;
        if (auth()->user()) {
            $userOrders = BreadOrder::where('user_id', auth()->id())
                ->where('delivery_date', $selectedDate)
                ->get();
        }
        
        // Get the user's previous orders (from most recent different date)
        $previousOrders = null;
        if (auth()->user()) {
            // Get the most recent order date that's different from the selected date
            $previousOrderDate = BreadOrder::where('user_id', auth()->id())
                ->where('delivery_date', '!=', $selectedDate)
                ->orderBy('delivery_date', 'desc')
                ->value('delivery_date');
                
            if ($previousOrderDate) {
                $previousOrders = BreadOrder::where('user_id', auth()->id())
                    ->where('delivery_date', $previousOrderDate)
                    ->get();
            }
        }
        
        // Get summary of all orders for the selected date (for admin view)
        $tomorrowSummary = [];
        if (auth()->user() && (auth()->user()->role === 'admin-user' || auth()->user()->role === 'admin-admin')) {
            $summaryData = BreadOrder::where('delivery_date', $selectedDate)
                ->select('bread_type_id', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('bread_type_id')
                ->get();
                
            // Convert to associative array
            foreach ($summaryData as $item) {
                $tomorrowSummary[$item->bread_type_id] = $item;
            }
        }
        
        return view('bread-orders.index', compact(
            'breadTypes', 
            'defaultDate', 
            'previousOrders', 
            'tomorrowSummary',
            'userOrders'
        ));
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'delivery_date' => 'required|date|after_or_equal:today',
                'bread_orders' => 'required|array',
                'bread_orders.*' => 'nullable|integer|min:0',
            ]);
            
            // Delete any existing orders for this user and date
            BreadOrder::where('user_id', auth()->id())
                      ->where('delivery_date', $validated['delivery_date'])
                      ->delete();
            
            // Create new orders
            foreach ($validated['bread_orders'] as $breadTypeId => $quantity) {
                if ($quantity > 0) {
                    BreadOrder::create([
                        'user_id' => auth()->id(),
                        'bread_type_id' => $breadTypeId,
                        'delivery_date' => $validated['delivery_date'],
                        'quantity' => $quantity,
                    ]);
                }
            }
            
            return redirect()->route('bread-orders.index', ['date' => $validated['delivery_date']])
                ->with('success', 'Нарачката е успешно зачувана за ' . $validated['delivery_date']);
                
        } catch (\Exception $e) {
            Log::error('Error saving bread order: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Се појави грешка при зачувување на нарачката: ' . $e->getMessage());
        }
    }
    
    public function summary()
    {
        // Only admins can view summary
        if (!auth()->user() || !(auth()->user()->role === 'admin-user' || auth()->user()->role === 'admin-admin')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get dates with orders
        $dates = BreadOrder::select('delivery_date')
                          ->groupBy('delivery_date')
                          ->orderBy('delivery_date', 'desc')
                          ->limit(10)
                          ->get()
                          ->pluck('delivery_date');
                          
        // If no dates found, use tomorrow
        if ($dates->isEmpty()) {
            $dates = collect([now()->addDay()->format('Y-m-d')]);
        }
                          
        // Get selected date (default to tomorrow if no date in request)
        $selectedDate = request('date', $dates->first());
        
        // Get all active bread types
        $breadTypes = BreadType::where('is_active', true)
                              ->orderBy('name')
                              ->get();
                              
        // Get order summary for selected date
        $orderSummary = BreadOrder::where('delivery_date', $selectedDate)
                                 ->select('bread_type_id', DB::raw('SUM(quantity) as total_quantity'))
                                 ->groupBy('bread_type_id')
                                 ->get()
                                 ->keyBy('bread_type_id');
                                 
        // Get detailed orders by user
        $userOrders = BreadOrder::where('delivery_date', $selectedDate)
                              ->with(['user', 'breadType'])
                              ->get()
                              ->groupBy('user_id');
                              
        return view('bread-orders.summary', compact('dates', 'selectedDate', 'breadTypes', 'orderSummary', 'userOrders'));
    }
}