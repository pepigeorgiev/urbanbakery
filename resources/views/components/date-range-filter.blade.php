{{-- Save this as resources/views/components/date-range-filter.blade.php --}}

<div class="date-range-filter bg-white shadow-md rounded p-4 mb-6">
    <h1 class="text-center text-blue-600 font-bold ">ПЕРИОДИЧЕН ПРЕГЛЕД</h1>
    <form method="GET" action="{{ route('summary.index') }}" class="flex flex-wrap items-center gap-4">
        <!-- Preserve existing parameters -->
        @if(request()->has('user_id'))
            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
        @endif
        
        
        <div class="flex flex-wrap items-center gap-2">
            <span class="font-medium">Период:</span>
            <input 
                type="date" 
                name="start_date" 
                value="{{ request('start_date', $date ?? now()->toDateString()) }}" 
                class="bg-white  border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 min-w-[140px]"
            >
            <span>-</span>
            <input 
                type="date" 
                name="end_date" 
                value="{{ request('end_date', $date ?? now()->toDateString()) }}" 
                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 min-w-[140px]"
            >
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Прикажи
            </button>
            
            @if(request()->has('start_date') || request()->has('end_date'))
                <a href="{{ route('summary.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Ресетирај
                </a>
            @endif
        </div>
    </form>
    
    @if(request()->has('start_date') && request()->has('end_date'))
        <div class="mt-2 text-center">
            <span class="font-semibold text-blue-600">
                Период: {{ \Carbon\Carbon::parse(request('start_date'))->format('d.m.Y') }} - 
                {{ \Carbon\Carbon::parse(request('end_date'))->format('d.m.Y') }}
            </span>
        </div>
    @endif
</div>