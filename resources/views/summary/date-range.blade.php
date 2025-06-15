@extends('layouts.app')

@php
    $breadTypesWithReturns = $breadTypesAnalysis ?? [];
    
    // Sort by total returned (descending)
    uasort($breadTypesWithReturns, function($a, $b) {
        return $b['total_returned'] <=> $a['total_returned'];
    });
@endphp

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

        <!-- Company Performance Analysis Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Анализа на перформанси на компании</h2>
                
                <!-- Performance view toggle controls -->
                <div class="flex space-x-2">
                    <button onclick="togglePerformanceView('best')" id="bestPerformersBtn" 
                        class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded hover:bg-green-200">
                        Најдобри купувачи
                    </button>
                    <button onclick="togglePerformanceView('worst')" id="worstPerformersBtn"
                        class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        Најлоши купувачи
                    </button>
                    <button onclick="togglePerformanceView('all')" id="allPerformanceBtn"
                        class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        Сите компании
                    </button>
                    <button onclick="togglePerformanceView('breadtypes')" id="breadTypesBtn"
                        class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        Видови леб
                    </button>
                </div>
            </div>

            <!-- Performance Summary Cards -->
            @if(isset($performanceSummary))
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-blue-800 font-semibold mb-2">Активни компании</h4>
                    <p class="text-2xl font-bold text-blue-600">{{ $performanceSummary['total_companies'] }}</p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="text-green-800 font-semibold mb-2">Вкупни продажби</h4>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($performanceSummary['total_sales'], 2) }} ден.</p>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-red-800 font-semibold mb-2">Вкупна загуба</h4>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($performanceSummary['total_return_loss'], 2) }} ден.</p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-yellow-800 font-semibold mb-2">Просечен поврат</h4>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($performanceSummary['average_return_percentage'], 1) }}%</p>
                </div>
            </div>
            @endif

            <!-- Best Performers View -->
            <div id="bestPerformersView" class="space-y-4">
                <h3 class="text-lg font-semibold text-green-800">Топ 10 најдобри купувачи</h3>
                <p class="text-sm text-gray-600 mb-4">Рангирани според комбинација од продажби, ефикасност и низок процент на поврат</p>
                
                <div class="bg-white shadow-md rounded overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Компанија</th>
                                <th class="px-4 py-3 text-center">Тип</th>
                                <th class="px-4 py-3 text-right">Продажби</th>
                                <th class="px-4 py-3 text-center">Ефикасност</th>
                                <th class="px-4 py-3 text-center">Поврат %</th>
                                <th class="px-4 py-3 text-right">Нето профит</th>
                                <th class="px-4 py-3 text-center">Резултат</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rank = 1; @endphp
                            @forelse($bestPerformers as $companyId => $company)
                                <tr class="border-t {{ $rank <= 3 ? 'bg-green-25' : '' }}">
                                    <td class="px-4 py-3 font-bold text-green-600">
                                        @if($rank == 1) 🥇 @elseif($rank == 2) 🥈 @elseif($rank == 3) 🥉 @else {{ $rank }} @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $company['company_name'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $company['company_type'] === 'cash' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $company['company_type'] === 'cash' ? 'Кеш' : 'Фактура' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600">
                                        {{ number_format($company['total_sales_amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-medium text-green-600">{{ number_format($company['efficiency_percentage'], 1) }}%</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm {{ $company['return_percentage'] < 5 ? 'text-green-600' : ($company['return_percentage'] < 15 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($company['return_percentage'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $company['net_profit'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($company['net_profit'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, $company['performance_score']) }}%"></div>
                                            </div>
                                            <span class="ml-2 text-xs">{{ number_format($company['performance_score'], 0) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @php $rank++; @endphp
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-center text-gray-500">Нема податоци за приказ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FIXED: Bread Types View - Now shows all data properly -->
            <div id="breadTypesView" class="hidden space-y-4">
                <h3 class="text-lg font-semibold text-blue-800">Анализа по видови леб</h3>
                <p class="text-sm text-gray-600 mb-4">Рангирани според вкупни поврати за целиот период</p>
                
                <div class="bg-white shadow-md rounded overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Вид на леб</th>
                                <th class="px-4 py-3 text-center">Компании</th>
                                <th class="px-4 py-3 text-center">Испорачано</th>
                                <th class="px-4 py-3 text-center">Поврат</th>
                                <th class="px-4 py-3 text-center">Поврат %</th>
                                <th class="px-4 py-3 text-right">Загуба</th>
                                <th class="px-4 py-3 text-center">Перформанси</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $breadRank = 1; @endphp
                            @forelse($breadTypesWithReturns as $breadTypeId => $breadTypeData)
                                @php
                                    $returnPercentage = $breadTypeData['total_delivered'] > 0 ? 
                                        ($breadTypeData['total_returned'] / $breadTypeData['total_delivered']) * 100 : 0;
                                    $performanceScore = max(0, 100 - $returnPercentage);
                                @endphp
                                <tr class="border-t bg-blue-25">
                                    <td class="px-4 py-3 font-bold text-blue-600">{{ $breadRank }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $breadTypeData['name'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ $breadTypeData['company_count'] }}</td>
                                    <td class="px-4 py-3 text-center">{{ number_format($breadTypeData['total_delivered']) }}</td>
                                    <td class="px-4 py-3 text-center text-red-600 font-semibold">{{ number_format($breadTypeData['total_returned']) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ $returnPercentage > 15 ? 'text-red-600 font-bold' : ($returnPercentage > 8 ? 'text-yellow-600' : 'text-green-600') }}">
                                            {{ number_format($returnPercentage, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-600 font-semibold">{{ number_format($breadTypeData['total_loss'], 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min(100, $performanceScore) }}%"></div>
                                            </div>
                                            <span class="ml-2 text-xs">{{ number_format($performanceScore, 0) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @php $breadRank++; @endphp
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-center text-gray-500">Нема податоци за приказ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Worst Performers View -->
            <div id="worstPerformersView" class="hidden space-y-4">
                <h3 class="text-lg font-semibold text-red-800">Топ 10 најлоши купувачи</h3>
                <p class="text-sm text-gray-600 mb-4">Рангирани според висок процент на поврат и загуби</p>
                
                <div class="bg-white shadow-md rounded overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Компанија</th>
                                <th class="px-4 py-3 text-center">Тип</th>
                                <th class="px-4 py-3 text-center">Испорачано</th>
                                <th class="px-4 py-3 text-center">Поврат</th>
                                <th class="px-4 py-3 text-center">Поврат %</th>
                                <th class="px-4 py-3 text-right">Загуба од поврат</th>
                                <th class="px-4 py-3 text-right">Продажби</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rank = 1; @endphp
                            @forelse($worstPerformers as $companyId => $company)
                                <tr class="border-t {{ $company['return_percentage'] > 20 ? 'bg-red-25' : '' }}">
                                    <td class="px-4 py-3 font-bold text-red-600">{{ $rank }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $company['company_name'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $company['company_type'] === 'cash' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $company['company_type'] === 'cash' ? 'Кеш' : 'Фактура' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ number_format($company['total_delivered']) }}</td>
                                    <td class="px-4 py-3 text-center font-semibold text-red-600">
                                        {{ number_format($company['total_returned']) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="font-bold {{ $company['return_percentage'] > 25 ? 'text-red-700' : ($company['return_percentage'] > 15 ? 'text-red-600' : 'text-yellow-600') }}">
                                            {{ number_format($company['return_percentage'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-red-600">
                                        {{ number_format($company['total_return_loss'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{ number_format($company['total_sales_amount'], 2) }}
                                    </td>
                                </tr>
                                @php $rank++; @endphp
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-3 text-center text-gray-500">Нема податоци за приказ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- All Companies Performance View -->
            <div id="allPerformanceView" class="hidden space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Комплетен преглед на сите компании</h3>
                <p class="text-sm text-gray-600 mb-4">Сите компании рангирани според продажби за периодот</p>
                
                <div class="bg-white shadow-md rounded overflow-hidden">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Компанија</th>
                                <th class="px-4 py-3 text-center">Тип</th>
                                <th class="px-4 py-3 text-center">Испорачано</th>
                                <th class="px-4 py-3 text-center">Поврат</th>
                                <th class="px-4 py-3 text-center">Гратис</th>
                                <th class="px-4 py-3 text-center">Нето продажба</th>
                                <th class="px-4 py-3 text-center">Поврат %</th>
                                <th class="px-4 py-3 text-center">Ефикасност %</th>
                                <th class="px-4 py-3 text-right">Продажби</th>
                                <th class="px-4 py-3 text-right">Загуба</th>
                                <th class="px-4 py-3 text-right">Профит</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allCompaniesPerformance as $companyId => $company)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $company['company_name'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $company['company_type'] === 'cash' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $company['company_type'] === 'cash' ? 'Кеш' : 'Фактура' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ number_format($company['total_delivered']) }}</td>
                                    <td class="px-4 py-3 text-center text-red-600">{{ number_format($company['total_returned']) }}</td>
                                    <td class="px-4 py-3 text-center text-orange-600">{{ number_format($company['total_gratis']) }}</td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ number_format($company['net_sold']) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ $company['return_percentage'] > 20 ? 'text-red-600 font-bold' : ($company['return_percentage'] > 10 ? 'text-yellow-600' : 'text-green-600') }}">
                                            {{ number_format($company['return_percentage'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ $company['efficiency_percentage'] > 80 ? 'text-green-600' : ($company['efficiency_percentage'] > 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($company['efficiency_percentage'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600">
                                        {{ number_format($company['total_sales_amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-600">
                                        {{ number_format($company['total_return_loss'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $company['net_profit'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($company['net_profit'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-3 text-center text-gray-500">Нема податоци за приказ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
function togglePerformanceView(viewType) {
    const bestView = document.getElementById('bestPerformersView');
    const worstView = document.getElementById('worstPerformersView');
    const allView = document.getElementById('allPerformanceView');
    const breadTypesView = document.getElementById('breadTypesView');
    const bestBtn = document.getElementById('bestPerformersBtn');
    const worstBtn = document.getElementById('worstPerformersBtn');
    const allBtn = document.getElementById('allPerformanceBtn');
    const breadTypesBtn = document.getElementById('breadTypesBtn');

    // Hide all views
    bestView.classList.add('hidden');
    worstView.classList.add('hidden');
    allView.classList.add('hidden');
    breadTypesView.classList.add('hidden');

    // Reset all button styles
    bestBtn.classList.remove('bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-blue-100', 'text-blue-800');
    worstBtn.classList.remove('bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-blue-100', 'text-blue-800');
    allBtn.classList.remove('bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-blue-100', 'text-blue-800');
    breadTypesBtn.classList.remove('bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-blue-100', 'text-blue-800');

    bestBtn.classList.add('bg-gray-100', 'text-gray-800');
    worstBtn.classList.add('bg-gray-100', 'text-gray-800');
    allBtn.classList.add('bg-gray-100', 'text-gray-800');
    breadTypesBtn.classList.add('bg-gray-100', 'text-gray-800');

    // Show selected view and update button
    if (viewType === 'best') {
        bestView.classList.remove('hidden');
        bestBtn.classList.remove('bg-gray-100', 'text-gray-800');
        bestBtn.classList.add('bg-green-100', 'text-green-800');
    } else if (viewType === 'worst') {
        worstView.classList.remove('hidden');
        worstBtn.classList.remove('bg-gray-100', 'text-gray-800');
        worstBtn.classList.add('bg-red-100', 'text-red-800');
    } else if (viewType === 'breadtypes') {
        breadTypesView.classList.remove('hidden');
        breadTypesBtn.classList.remove('bg-gray-100', 'text-gray-800');
        breadTypesBtn.classList.add('bg-blue-100', 'text-blue-800');
    } else {
        allView.classList.remove('hidden');
        allBtn.classList.remove('bg-gray-100', 'text-gray-800');
        allBtn.classList.add('bg-blue-100', 'text-blue-800');
    }
}

function toggleBreadBreakdown(companyKey) {
    const breakdown = document.getElementById(companyKey + '-breakdown');
    const icon = document.getElementById(companyKey + '-icon');
    
    if (breakdown.classList.contains('hidden')) {
        breakdown.classList.remove('hidden');
        icon.textContent = '▼';
    } else {
        breakdown.classList.add('hidden');
        icon.textContent = '▶';
    }
}
</script>
@endsection