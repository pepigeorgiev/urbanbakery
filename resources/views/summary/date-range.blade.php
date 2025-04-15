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
@endsection