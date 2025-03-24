@extends('layouts.app')

@section('content')
<div class="container mx-auto px-2 md:px-4">
    <div class="card">
        <div class="card-header flex justify-between items-center flex-wrap gap-2 bg-blue-50">
            <div>
                <h2 class="text-xl font-bold">Нарачка на леб</h2>
                <p class="text-sm text-gray-600">Внесете ги количините што ви требаат од секој тип на леб</p>
            </div>
            
            @if(auth()->user() && (auth()->user()->role === 'admin-user' || auth()->user()->role === 'admin-admin'))
                <a href="{{ route('bread-orders.summary') }}" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded text-sm">
                    <i class="fas fa-list mr-1"></i> Преглед на сите нарачки
                </a>
            @endif
        </div>
        
        <div class="card-body">
            <form action="{{ route('bread-orders.store') }}" method="POST">
                @csrf
                
                <div class="mb-4 bg-gray-50 p-3 rounded border">
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Датум на испорака</label>
                    <input 
                        type="date" 
                        name="delivery_date" 
                        id="delivery_date"
                        class="w-full md:w-auto p-2 border rounded"
                        value="{{ $defaultDate }}"
                        min="{{ now()->format('Y-m-d') }}"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-1">Изберете го датумот за кој правите нарачка</p>
                </div>
                
                @if(auth()->user() && (auth()->user()->role === 'admin-user' || auth()->user()->role === 'admin-admin') && isset($tomorrowSummary) && !empty($tomorrowSummary))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">
                        <h3 class="font-bold text-green-800 mb-2">
                            <i class="fas fa-calculator mr-1"></i> Вкупни нарачки за {{ \Carbon\Carbon::parse($defaultDate)->format('d.m.Y') }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-2">Преглед на вкупните количини нарачани од сите корисници</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                            @foreach($breadTypes as $breadType)
                                <div class="flex justify-between p-2 bg-white rounded shadow">
                                    <span class="font-medium">{{ $breadType->name }}:</span>
                                    <span class="font-bold">
                                        @if(isset($tomorrowSummary[$breadType->id]))
                                            @if(is_array($tomorrowSummary[$breadType->id]))
                                                {{ $tomorrowSummary[$breadType->id]['total_quantity'] ?? 0 }}
                                            @else
                                                {{ $tomorrowSummary[$breadType->id]->total_quantity ?? 0 }}
                                            @endif
                                        @else
                                            0
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="bg-blue-50 border rounded p-3 mb-4">
                    <div class="flex justify-between items-center mb-2 border-b pb-2">
                        <h3 class="font-bold text-blue-800">
                            <i class="fas fa-bread-slice mr-1"></i> Вашата нарачка
                        </h3>
                        
                        @if(isset($previousOrders) && $previousOrders && count($previousOrders) > 0)
                            <button 
                                type="button" 
                                id="load-previous" 
                                class="text-sm bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded"
                            >
                                <i class="fas fa-sync-alt mr-1"></i> Користи ја претходната нарачка
                            </button>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($breadTypes as $breadType)
                            @php
                                // Get the current value for this bread type
                                $currentQuantity = 0;
                                
                                // Check if there's an existing order for this date and bread type
                                if (isset($userOrders)) {
                                    foreach ($userOrders as $order) {
                                        if ($order->bread_type_id == $breadType->id) {
                                            $currentQuantity = $order->quantity;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            
                            <div class="bg-white p-3 rounded shadow">
                                <div class="flex justify-between mb-1">
                                    <label for="bread_{{ $breadType->id }}" class="font-medium">
                                        {{ $breadType->name }}
                                    </label>
                                    <span class="text-xs text-gray-500">{{ $breadType->code }}</span>
                                </div>
                                
                                <div class="flex items-center">
                                    <button 
                                        type="button" 
                                        class="decrement-btn px-3 py-1 bg-gray-200 rounded-l hover:bg-gray-300"
                                        data-bread-id="{{ $breadType->id }}"
                                    >-</button>
                                    
                                    <input 
                                        type="number" 
                                        name="bread_orders[{{ $breadType->id }}]" 
                                        id="bread_{{ $breadType->id }}" 
                                        class="border-t border-b text-center p-1 w-16" 
                                        min="0" 
                                        value="{{ $currentQuantity }}"
                                    >
                                    
                                    <button 
                                        type="button" 
                                        class="increment-btn px-3 py-1 bg-gray-200 rounded-r hover:bg-gray-300"
                                        data-bread-id="{{ $breadType->id }}"
                                    >+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-save mr-1"></i> Зачувај нарачка
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle increment/decrement buttons
        document.querySelectorAll('.increment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const breadId = this.getAttribute('data-bread-id');
                const input = document.getElementById('bread_' + breadId);
                input.value = parseInt(input.value || 0) + 1;
            });
        });
        
        document.querySelectorAll('.decrement-btn').forEach(button => {
            button.addEventListener('click', function() {
                const breadId = this.getAttribute('data-bread-id');
                const input = document.getElementById('bread_' + breadId);
                input.value = Math.max(0, parseInt(input.value || 0) - 1);
            });
        });
        
        // Load previous orders
        const loadPreviousBtn = document.getElementById('load-previous');
        if (loadPreviousBtn) {
            loadPreviousBtn.addEventListener('click', function() {
                @if(isset($previousOrders) && $previousOrders && count($previousOrders) > 0)
                    const previousOrders = {
                        @foreach($previousOrders as $order)
                            @if(is_object($order) && isset($order->bread_type_id) && isset($order->quantity))
                                {{ $order->bread_type_id }}: {{ $order->quantity }},
                            @endif
                        @endforeach
                    };
                    
                    // Set input values
                    Object.keys(previousOrders).forEach(breadId => {
                        const input = document.getElementById('bread_' + breadId);
                        if (input) {
                            input.value = previousOrders[breadId];
                        }
                    });
                @endif
            });
        }
        
        // Change date event listener
        document.getElementById('delivery_date').addEventListener('change', function() {
            // Reload the page with the new date
            window.location.href = '{{ route("bread-orders.index") }}?date=' + this.value;
        });
    });
</script>
@endsection