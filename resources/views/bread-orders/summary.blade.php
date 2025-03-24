@extends('layouts.app')

@section('content')
<div class="container mx-auto px-2 md:px-4" id="print-content">
    <div class="card">
        <div class="card-header flex justify-between items-center flex-wrap gap-2 no-print bg-green-50">
            <div>
                <h2 class="text-xl font-bold">Преглед на сите нарачки</h2>
                <p class="text-sm text-gray-600">Преглед и печатење на сите нарачки на леб од сите корисници</p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('bread-orders.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded text-sm">
                    <i class="fas fa-bread-slice mr-1"></i> Назад кон моја нарачка
                </a>
                
                @if(count($userOrders) > 0)
                    <button 
                        onclick="printReport()" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded text-sm"
                    >
                        <i class="fas fa-print mr-1"></i> Печати извештај
                    </button>
                @endif
            </div>
        </div>
        
        <div class="card-body">
            <div class="mb-4 p-3 bg-gray-50 border rounded no-print">
                <form action="{{ route('bread-orders.summary') }}" method="GET" class="flex flex-wrap gap-2 items-end">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Изберете датум за преглед</label>
                        <select name="date" id="date" class="p-2 border rounded" onchange="this.form.submit()">
                            @foreach($dates as $date)
                                <option value="{{ $date }}" {{ $selectedDate == $date ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="bg-gray-200 hover:bg-gray-300 py-2 px-3 rounded">
                        <i class="fas fa-filter mr-1"></i> Филтрирај
                    </button>
                </form>
            </div>
            
            <div class="print-header" style="display: none;">
                <h1 class="text-center text-xl font-bold py-2">ДНЕВЕН ИЗВЕШТАЈ - НАРАЧКА НА ЛЕБ</h1>
                <p class="text-center pb-2">Датум на испорака: {{ \Carbon\Carbon::parse($selectedDate)->format('d.m.Y') }}</p>
                <hr class="border-t border-gray-300 my-4">
            </div>
            
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded print-section">
                <h3 class="font-bold text-green-800 mb-3">
                    <i class="fas fa-calculator mr-1"></i> Вкупни нарачки за {{ \Carbon\Carbon::parse($selectedDate)->format('d.m.Y') }}
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @forelse($breadTypes as $breadType)
                        <div class="flex justify-between p-3 bg-white rounded shadow">
                            <span class="font-medium">{{ $breadType->name }}:</span>
                            <span class="font-bold">{{ $orderSummary->get($breadType->id)->total_quantity ?? 0 }}</span>
                        </div>
                    @empty
                        <div class="col-span-full p-3 text-center text-gray-500">
                            Нема активни типови на леб
                        </div>
                    @endforelse
                </div>
            </div>
            
            <div class="print-section">
                <h3 class="font-bold mb-3 border-b pb-2">
                    <i class="fas fa-users mr-1"></i> Детални нарачки по корисник
                </h3>
                
                @forelse($userOrders as $userId => $orders)
                    <div class="mb-4 p-4 bg-white rounded shadow">
                        <h4 class="font-medium border-b pb-2 mb-3">
                            {{ $orders->first()->user->name ?? 'Непознат корисник' }}
                        </h4>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                            @foreach($orders as $order)
                                <div class="flex justify-between p-2 bg-gray-50 rounded">
                                    <span>{{ $order->breadType->name ?? 'Непознат леб' }}:</span>
                                    <span class="font-bold">{{ $order->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500 bg-gray-50 rounded">
                        Нема нарачки за избраниот датум
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6 print-footer" style="display: none;">
                <hr class="border-t border-gray-300 my-4">
                <p class="text-center text-sm">Извештајот е генериран на {{ now()->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .no-print {
            display: none !important;
        }
        .print-header, .print-footer {
            display: block !important;
            visibility: visible;
        }
        #print-content, #print-content * {
            visibility: visible;
        }
        .print-section, .print-section * {
            visibility: visible;
        }
        #print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 15px;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        .bg-green-50, .bg-blue-50 {
            background-color: white !important;
            border: none !important;
        }
        .shadow {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
    }
</style>

<script>
    function printReport() {
        window.print();
    }
</script>
@endsection