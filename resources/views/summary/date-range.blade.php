@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Периодичен преглед {{ \Carbon\Carbon::parse($startDate)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d.m.Y') }}</h1>
    
    <div class="container mx-auto px-0 py-6">
        <div class="mb-6">
            <div class="flex flex-wrap items-center gap-4">
                @if($currentUser->isAdmin() || $currentUser->role === 'super_admin')
                    <form method="GET" action="{{ route('summary.date-range') }}" class="flex items-center">
                        <input type="hidden" name="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" value="{{ $endDate }}">
                        
                        <select 
                            name="user_id" 
                            onchange="this.form.submit()"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        >
                            <option value="">Сите корисници</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <!-- Date Range Filter -->
                @include('components.date-range-filter')
                
                <!-- Link back to daily view -->
                <a href="{{ route('summary.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                    Врати се на дневен преглед
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Bread Card -->
            <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-blue-500">
                <h3 class="text-lg font-semibold text-blue-800 mb-2">Вкупно продаден леб</h3>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalQuantity) }} производи</p>
                <p class="text-xl text-gray-600 mt-2">{{ number_format($totalInvoiceAmount+$totalCashAmount, 2) }} денари</p>
            </div>
            
            <!-- Cash Companies Card -->
            <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-green-800 mb-2">Кеш плаќања</h3>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalCashAmount, 2) }} денари</p>
                <p class="text-xl text-gray-600 mt-2">{{ count($cashCompanies) }} компании</p>
            </div>
            
            <!-- Invoice Companies Card -->
            <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-purple-500">
                <h3 class="text-lg font-semibold text-purple-800 mb-2">Фактура плаќања</h3>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalInvoiceAmount, 2) }} денари</p>
                <p class="text-xl text-gray-600 mt-2">{{ count($invoiceCompanies) }} компании</p>
            </div>
        </div>
        
        <!-- Old Bread Card -->
        <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-yellow-500 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Вчерашен леб</h3>
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($oldBreadTotal, 2) }} денари</p>
                </div>
                <div class="text-center bg-yellow-100 rounded-lg p-4">
                    <p class="text-lg font-medium text-yellow-800">Продадени производи</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ number_format($oldBreadSold) }}</p>
                </div>
            </div>
        </div>
        
        <!-- Grand Total Card -->
        <div class="bg-white shadow-md rounded-lg p-5 border-l-4 border-red-500 mb-8">
            <h3 class="text-xl font-semibold text-red-800 mb-2">Вкупно приходи за периодот</h3>
            <p class="text-4xl font-bold text-gray-800">{{ number_format($totalInvoiceAmount+$totalCashAmount+$oldBreadTotal, 2) }} денари</p>
        </div>

        <!-- Bread Summary Table -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Детална продажба по видови леб</h2>
            <div class="bg-white shadow-md rounded overflow-hidden">
                <table class="w-full table-auto">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">Вид на леб</th>
                            <th class="px-4 py-2 text-center">Количина</th>
                            <th class="px-4 py-2 text-center">Цена</th>
                            <th class="px-4 py-2 text-right">Вкупно</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($breadSummary as $bread)
                            <tr class="border-t">
                                <td class="px-4 py-2 font-medium">{{ $bread['bread_type'] }}</td>
                                <td class="px-4 py-2 text-center">{{ number_format($bread['quantity']) }}</td>
                                <td class="px-4 py-2 text-center">{{ number_format($bread['price'], 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($bread['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-center">Нема податоци за приказ</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-4 py-2 font-bold">Вкупно</td>
                            <td class="px-4 py-2 text-center font-bold">{{ number_format($totalQuantity) }}</td>
                            <td class="px-4 py-2"></td>
                            <td class="px-4 py-2 text-right font-bold">{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Анализа на вратен леб за периодот</h2>
                
                <!-- View toggle controls -->
                <div class="flex space-x-2">
                    <button onclick="toggleReturnView('companies')" id="companiesViewBtn" 
                        class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                        По компании
                    </button>
                    <button onclick="toggleReturnView('breadtypes')" id="breadtypesViewBtn"
                        class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        По видови леб
                    </button>
                </div>
            </div>

            <!-- Page Size Selector -->
            <div class="mb-4 flex justify-between items-center">
                <form method="GET" class="inline-flex items-center space-x-2">
                    <!-- Preserve all current parameters -->
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                    @if($selectedUserId)<input type="hidden" name="user_id" value="{{ $selectedUserId }}">@endif
                    
                    <label class="text-sm text-gray-600">Прикажи:</label>
                    <select name="per_page" onchange="this.form.submit()" class="text-sm border rounded px-2 py-1">
                        <option value="50" {{ request('per_page', 100) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page', 100) == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ request('per_page', 100) == 500 ? 'selected' : '' }}>500</option>
                    </select>
                    <label class="text-sm text-gray-600">транзакции по страна</label>
                </form>

                <div class="text-sm text-gray-600">
                    Вкупно {{ $returnedBreadTransactions->total() }} транзакции со враќања
                </div>
            </div>

            @php
                // Group returned bread transactions by company (aggregate all dates)
                $companiesWithReturns = [];
                $breadTypesWithReturns = [];
                $totalReturnedQuantity = 0;
                $totalReturnLoss = 0;

                foreach($returnedBreadTransactions as $transaction) {
                    $companyId = $transaction->company_id;
                    $breadTypeId = $transaction->bread_type_id;
                    $companyName = $transaction->company->name;
                    $breadTypeName = $transaction->breadType->name;
                    $returned = $transaction->returned;
                    $delivered = $transaction->delivered;
                    
                    $priceData = $transaction->breadType->getPriceForCompany($transaction->company_id, $transaction->transaction_date);
                    $price = $priceData['price'];
                    $returnLoss = $returned * $price;
                    
                    // Group by company
                    if (!isset($companiesWithReturns[$companyId])) {
                        $companiesWithReturns[$companyId] = [
                            'name' => $companyName,
                            'type' => $transaction->company->type,
                            'total_delivered' => 0,
                            'total_returned' => 0,
                            'total_loss' => 0,
                            'bread_types' => []
                        ];
                    }
                    
                    $companiesWithReturns[$companyId]['total_delivered'] += $delivered;
                    $companiesWithReturns[$companyId]['total_returned'] += $returned;
                    $companiesWithReturns[$companyId]['total_loss'] += $returnLoss;
                    
                    // Track bread types per company
                    if (!isset($companiesWithReturns[$companyId]['bread_types'][$breadTypeId])) {
                        $companiesWithReturns[$companyId]['bread_types'][$breadTypeId] = [
                            'name' => $breadTypeName,
                            'delivered' => 0,
                            'returned' => 0,
                            'loss' => 0
                        ];
                    }
                    
                    $companiesWithReturns[$companyId]['bread_types'][$breadTypeId]['delivered'] += $delivered;
                    $companiesWithReturns[$companyId]['bread_types'][$breadTypeId]['returned'] += $returned;
                    $companiesWithReturns[$companyId]['bread_types'][$breadTypeId]['loss'] += $returnLoss;
                    
                    // Group by bread type
                    if (!isset($breadTypesWithReturns[$breadTypeId])) {
                        $breadTypesWithReturns[$breadTypeId] = [
                            'name' => $breadTypeName,
                            'total_delivered' => 0,
                            'total_returned' => 0,
                            'total_loss' => 0,
                            'companies' => []
                        ];
                    }
                    
                    $breadTypesWithReturns[$breadTypeId]['total_delivered'] += $delivered;
                    $breadTypesWithReturns[$breadTypeId]['total_returned'] += $returned;
                    $breadTypesWithReturns[$breadTypeId]['total_loss'] += $returnLoss;
                    
                    // Track companies per bread type
                    if (!isset($breadTypesWithReturns[$breadTypeId]['companies'][$companyId])) {
                        $breadTypesWithReturns[$breadTypeId]['companies'][$companyId] = [
                            'name' => $companyName,
                            'type' => $transaction->company->type,
                            'delivered' => 0,
                            'returned' => 0,
                            'loss' => 0
                        ];
                    }
                    
                    $breadTypesWithReturns[$breadTypeId]['companies'][$companyId]['delivered'] += $delivered;
                    $breadTypesWithReturns[$breadTypeId]['companies'][$companyId]['returned'] += $returned;
                    $breadTypesWithReturns[$breadTypeId]['companies'][$companyId]['loss'] += $returnLoss;
                    
                    $totalReturnedQuantity += $returned;
                    $totalReturnLoss += $returnLoss;
                }

                // Sort companies by total returned (descending)
                uasort($companiesWithReturns, function($a, $b) {
                    return $b['total_returned'] <=> $a['total_returned'];
                });

                // Sort bread types by total returned (descending)
                uasort($breadTypesWithReturns, function($a, $b) {
                    return $b['total_returned'] <=> $a['total_returned'];
                });
            @endphp

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-red-800 font-semibold mb-2">Вратен леб (оваа страна)</h4>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($totalReturnedQuantity) }}</p>
                </div>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h4 class="text-orange-800 font-semibold mb-2">Компании (оваа страна)</h4>
                    <p class="text-2xl font-bold text-orange-600">{{ count($companiesWithReturns) }}</p>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-yellow-800 font-semibold mb-2">Видови леб (оваа страна)</h4>
                    <p class="text-2xl font-bold text-yellow-600">{{ count($breadTypesWithReturns) }}</p>
                </div>

                <div class="bg-red-100 border border-red-300 rounded-lg p-4">
                    <h4 class="text-red-800 font-semibold mb-2">Загуба (оваа страна)</h4>
                    <p class="text-2xl font-bold text-red-700">{{ number_format($totalReturnLoss, 2) }} ден.</p>
                </div>
            </div>

            <!-- Companies View -->
            <div id="companiesView" class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Поврат по компании (страна {{ $returnedBreadTransactions->currentPage() }})</h3>
                
                @forelse($companiesWithReturns as $companyId => $companyData)
                    @php
                        $returnPercentage = $companyData['total_delivered'] > 0 ? 
                            ($companyData['total_returned'] / $companyData['total_delivered']) * 100 : 0;
                    @endphp
                    
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border-l-4 
                        {{ $returnPercentage > 20 ? 'border-red-500' : ($returnPercentage > 10 ? 'border-yellow-500' : 'border-gray-300') }}">
                        
                        <!-- Company Header -->
                        <div class="bg-gray-50 px-6 py-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-xl font-semibold">{{ $companyData['name'] }}</h4>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $companyData['type'] === 'cash' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $companyData['type'] === 'cash' ? 'Кеш' : 'Фактура' }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">Испорачано: {{ number_format($companyData['total_delivered']) }}</div>
                                    <div class="text-lg font-bold text-red-600">
                                        Поврат: {{ number_format($companyData['total_returned']) }}
                                        <span class="text-sm">({{ number_format($returnPercentage, 1) }}%)</span>
                                    </div>
                                    <div class="text-sm text-red-600 font-semibold">
                                        Загуба од поврат: {{ number_format($companyData['total_loss'], 2) }} ден.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bread Types for this Company -->
                        <div class="px-6 py-4">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr class="text-left text-sm text-gray-600">
                                        <th class="pb-2">Вид на леб</th>
                                        <th class="pb-2 text-center">Испорачано</th>
                                        <th class="pb-2 text-center">Поврат</th>
                                        <th class="pb-2 text-center">Поврат во %</th>
                                        <th class="pb-2 text-right">Загуба</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companyData['bread_types'] as $breadTypeData)
                                        @php
                                            $breadReturnPercentage = $breadTypeData['delivered'] > 0 ? 
                                                ($breadTypeData['returned'] / $breadTypeData['delivered']) * 100 : 0;
                                        @endphp
                                        <tr class="border-t border-gray-100">
                                            <td class="py-2 font-medium">{{ $breadTypeData['name'] }}</td>
                                            <td class="py-2 text-center">{{ number_format($breadTypeData['delivered']) }}</td>
                                            <td class="py-2 text-center">
                                                <span class="font-semibold text-red-600">{{ number_format($breadTypeData['returned']) }}</span>
                                            </td>
                                            <td class="py-2 text-center">
                                                <span class="text-sm {{ $breadReturnPercentage > 20 ? 'text-red-600 font-bold' : ($breadReturnPercentage > 10 ? 'text-yellow-600 font-semibold' : 'text-gray-600') }}">
                                                    {{ number_format($breadReturnPercentage, 1) }}%
                                                </span>
                                            </td>
                                            <td class="py-2 text-right">
                                                <span class="font-semibold text-red-600">{{ number_format($breadTypeData['loss'], 2) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-md rounded-lg p-6 text-center text-gray-500">
                        Нема компании со вратен леб на оваа страна
                    </div>
                @endforelse
            </div>

            <!-- Bread Types View -->
            <div id="breadtypesView" class="hidden space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Поврат по видови леб (страна {{ $returnedBreadTransactions->currentPage() }})</h3>
                
                @forelse($breadTypesWithReturns as $breadTypeId => $breadTypeData)
                    @php
                        $returnPercentage = $breadTypeData['total_delivered'] > 0 ? 
                            ($breadTypeData['total_returned'] / $breadTypeData['total_delivered']) * 100 : 0;
                    @endphp
                    
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border-l-4 
                        {{ $returnPercentage > 20 ? 'border-red-500' : ($returnPercentage > 10 ? 'border-yellow-500' : 'border-gray-300') }}">
                        
                        <!-- Bread Type Header -->
                        <div class="bg-gray-50 px-6 py-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-xl font-semibold">{{ $breadTypeData['name'] }}</h4>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Поврат од {{ count($breadTypeData['companies']) }} 
                                        {{ count($breadTypeData['companies']) == 1 ? 'компанија' : 'компании' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">Испорачано: {{ number_format($breadTypeData['total_delivered']) }}</div>
                                    <div class="text-lg font-bold text-red-600">
                                        Поврат: {{ number_format($breadTypeData['total_returned']) }}
                                        <span class="text-sm">({{ number_format($returnPercentage, 1) }}%)</span>
                                    </div>
                                    <div class="text-sm text-red-600 font-semibold">
                                        Загуба од поврат: {{ number_format($breadTypeData['total_loss'], 2) }} ден.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Companies for this Bread Type -->
                        <div class="px-6 py-4">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr class="text-left text-sm text-gray-600">
                                        <th class="pb-2">Компанија</th>
                                        <th class="pb-2 text-center">Тип</th>
                                        <th class="pb-2 text-center">Испорачано</th>
                                        <th class="pb-2 text-center">Поврат</th>
                                        <th class="pb-2 text-center">Поврат во %</th>
                                        <th class="pb-2 text-right">Загуба</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($breadTypeData['companies'] as $companyData)
                                        @php
                                            $companyReturnPercentage = $companyData['delivered'] > 0 ? 
                                                ($companyData['returned'] / $companyData['delivered']) * 100 : 0;
                                        @endphp
                                        <tr class="border-t border-gray-100">
                                            <td class="py-2 font-medium">{{ $companyData['name'] }}</td>
                                            <td class="py-2 text-center">
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                                    {{ $companyData['type'] === 'cash' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                                    {{ $companyData['type'] === 'cash' ? 'Кеш' : 'Фактура' }}
                                                </span>
                                            </td>
                                            <td class="py-2 text-center">{{ number_format($companyData['delivered']) }}</td>
                                            <td class="py-2 text-center">
                                                <span class="font-semibold text-red-600">{{ number_format($companyData['returned']) }}</span>
                                            </td>
                                            <td class="py-2 text-center">
                                                <span class="text-sm {{ $companyReturnPercentage > 20 ? 'text-red-600 font-bold' : ($companyReturnPercentage > 10 ? 'text-yellow-600 font-semibold' : 'text-gray-600') }}">
                                                    {{ number_format($companyReturnPercentage, 1) }}%
                                                </span>
                                            </td>
                                            <td class="py-2 text-right">
                                                <span class="font-semibold text-red-600">{{ number_format($companyData['loss'], 2) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-md rounded-lg p-6 text-center text-gray-500">
                        Нема видови леб со враќања на оваа страна
                    </div>
                @endforelse
            </div>

            <!-- PAGINATION CONTROLS -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Прикажани се {{ $returnedBreadTransactions->firstItem() ?? 0 }} до {{ $returnedBreadTransactions->lastItem() ?? 0 }} 
                    од {{ $returnedBreadTransactions->total() }} транзакции со враќања
                </div>
                
                <div class="flex items-center space-x-2">
                    {{-- Previous Page Link --}}
                    @if ($returnedBreadTransactions->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                            « Претходна
                        </span>
                    @else
                        <a href="{{ $returnedBreadTransactions->appends(request()->query())->previousPageUrl() }}" 
                           class="px-3 py-2 text-sm text-blue-600 bg-blue-100 rounded hover:bg-blue-200">
                            « Претходна
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $returnedBreadTransactions->currentPage() - 2);
                        $end = min($returnedBreadTransactions->lastPage(), $returnedBreadTransactions->currentPage() + 2);
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $returnedBreadTransactions->currentPage())
                            <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded">
                                {{ $i }}
                            </span>
                        @else
                            <a href="{{ $returnedBreadTransactions->appends(request()->query())->url($i) }}" 
                               class="px-3 py-2 text-sm text-blue-600 bg-blue-100 rounded hover:bg-blue-200">
                                {{ $i }}
                            </a>
                        @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if ($returnedBreadTransactions->hasMorePages())
                        <a href="{{ $returnedBreadTransactions->appends(request()->query())->nextPageUrl() }}" 
                           class="px-3 py-2 text-sm text-blue-600 bg-blue-100 rounded hover:bg-blue-200">
                            Следна »
                        </a>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                            Следна »
                        </span>
                    @endif
                </div>
            </div>

            <!-- Summary for current page -->
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-blue-800">
                        <strong>Страна {{ $returnedBreadTransactions->currentPage() }} од {{ $returnedBreadTransactions->lastPage() }}</strong>
                    </span>
                    <span class="text-blue-600">
                        Вкупно {{ $returnedBreadTransactions->total() }} транзакции со враќања за овој период
                    </span>
                </div>
            </div>
        </div>

        <!-- Company Tables -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Cash Companies -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Компании со кеш плаќање</h2>
                <div class="bg-white shadow-md rounded overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Компанија</th>
                                <th class="px-4 py-2 text-right">Вкупно</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashCompanies as $company)
                                <tr class="border-t">
                                    <td class="px-4 py-2 font-medium">{{ $company['name'] }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($company['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-center">Нема податоци за приказ</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-2 font-bold">Вкупно</td>
                                <td class="px-4 py-2 text-right font-bold">{{ number_format($totalCashAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Invoice Companies -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Компании со плаќање на фактура</h2>
                <div class="bg-white shadow-md rounded overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Компанија</th>
                                <th class="px-4 py-2 text-right">Вкупно</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoiceCompanies as $company)
                                <tr class="border-t">
                                    <td class="px-4 py-2 font-medium">{{ $company['name'] }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($company['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-center">Нема податоци за приказ</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-2 font-bold">Вкупно</td>
                                <td class="px-4 py-2 text-right font-bold">{{ number_format($totalInvoiceAmount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleReturnView(viewType) {
    const companiesView = document.getElementById('companiesView');
    const breadtypesView = document.getElementById('breadtypesView');
    const companiesBtn = document.getElementById('companiesViewBtn');
    const breadtypesBtn = document.getElementById('breadtypesViewBtn');
    
    if (viewType === 'companies') {
        companiesView.classList.remove('hidden');
        breadtypesView.classList.add('hidden');
        companiesBtn.classList.remove('bg-gray-100', 'text-gray-800');
        companiesBtn.classList.add('bg-blue-100', 'text-blue-800');
        breadtypesBtn.classList.remove('bg-blue-100', 'text-blue-800');
        breadtypesBtn.classList.add('bg-gray-100', 'text-gray-800');
    } else {
        companiesView.classList.add('hidden');
        breadtypesView.classList.remove('hidden');
        breadtypesBtn.classList.remove('bg-gray-100', 'text-gray-800');
        breadtypesBtn.classList.add('bg-blue-100', 'text-blue-800');
        companiesBtn.classList.remove('bg-blue-100', 'text-blue-800');
        companiesBtn.classList.add('bg-gray-100', 'text-gray-800');
    }
}
</script>
@endsection