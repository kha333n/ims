<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Report' }} — IMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; }
            main { padding: 0 !important; }
            @page { margin: 1cm; }
        }
        .print-only { display: none; }
    </style>
</head>
<body class="h-full bg-content flex flex-col" x-data>

    {{-- Top bar (hidden in print) --}}
    <div class="no-print bg-navy-800 text-white shrink-0">
        <div class="flex items-center h-10 px-4 gap-4">
            <a href="javascript:history.back()" class="flex items-center gap-1.5 text-sm text-gray-300 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="h-5 w-px bg-navy-600"></div>
            {{-- Reports dropdown --}}
            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                <button class="flex items-center gap-1 text-sm text-gray-200 hover:text-white transition-colors h-10">
                    Reports
                    <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                @php
                    $currentPath = request()->path();
                    $reportLinks = [
                        ['route' => 'reports.item-sales', 'label' => 'Item Sale Report'],
                        ['route' => 'reports.item-detail', 'label' => 'Item Detail Report'],
                        ['route' => 'reports.daily-recovery', 'label' => 'Daily Recovery'],
                        ['route' => 'reports.monthly-recovery', 'label' => 'Monthly Recovery'],
                        ['route' => 'reports.returns', 'label' => 'Return Report'],
                        ['route' => 'reports.salesman', 'label' => 'Salesman Report'],
                        ['route' => 'reports.inventory', 'label' => 'Inventory Status'],
                        ['route' => 'reports.customer', 'label' => 'Customer Account'],
                        ['route' => 'reports.defaulters', 'label' => 'Defaulter Report'],
                    ];
                @endphp
                <div x-show="open" x-transition:enter="transition ease-out duration-75" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute left-0 top-full z-50 min-w-52 bg-navy-800 border border-navy-700 shadow-2xl py-1" style="display:none;">
                    @foreach ($reportLinks as $link)
                        @php $isActive = str_contains($currentPath, str_replace('reports.', 'reports/', $link['route'])); @endphp
                        <a href="{{ route($link['route']) }}"
                           class="block px-4 py-1.5 text-sm whitespace-nowrap {{ $isActive ? 'bg-navy-600 text-white font-medium' : 'text-gray-300 hover:bg-navy-600 hover:text-white' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            {{-- Financial dropdown --}}
            <div class="relative" x-data="{ fopen: false }" @mouseenter="fopen = true" @mouseleave="fopen = false">
                <button class="flex items-center gap-1 text-sm text-gray-200 hover:text-white transition-colors h-10">
                    Financial
                    <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                @php
                    $finLinks = [
                        ['route' => 'financial.cash-book', 'label' => 'Daily Cash Book'],
                        ['route' => 'financial.ledger', 'label' => 'Financial Ledger'],
                        ['route' => 'financial.profit-loss', 'label' => 'Profit & Loss'],
                        ['route' => 'financial.credit-debit', 'label' => 'Credit & Debit'],
                        ['route' => 'financial.collections', 'label' => 'Collections'],
                        ['route' => 'financial.receivables', 'label' => 'Receivables'],
                        ['route' => 'financial.losses', 'label' => 'Losses'],
                        ['route' => 'financial.supplier-expenses', 'label' => 'Supplier Expenses'],
                        ['route' => 'financial.commissions', 'label' => 'Commissions'],
                        ['route' => 'financial.inventory-valuation', 'label' => 'Inventory Valuation'],
                    ];
                @endphp
                <div x-show="fopen" x-transition.opacity.duration.150ms class="absolute left-0 top-full z-50 min-w-52 bg-navy-800 border border-navy-700 shadow-2xl py-1" style="display:none;">
                    @foreach ($finLinks as $fl)
                        @php $flActive = str_contains($currentPath, str_replace('financial.', 'financial/', $fl['route'])); @endphp
                        <a href="{{ route($fl['route']) }}" class="block px-4 py-1.5 text-sm whitespace-nowrap {{ $flActive ? 'bg-navy-600 text-white font-medium' : 'text-gray-300 hover:bg-navy-600 hover:text-white' }}">{{ $fl['label'] }}</a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-300 hover:text-white transition-colors">Dashboard</a>
            <div class="flex-1"></div>
            <button onclick="window.print()" class="text-xs text-white px-3 py-1 rounded bg-green-600 hover:bg-green-500 transition-colors font-medium">
                Print
            </button>
        </div>
    </div>

    {{-- Print-only header --}}
    <div class="print-only text-center mb-3 pt-2">
        <h1 class="text-lg font-bold">{{ \App\Models\Setting::get('company_name', 'Installment Management System') }}</h1>
        <p class="text-xs text-gray-500">{{ \App\Models\Setting::get('company_address', '') }}</p>
    </div>

    {{-- Report Content --}}
    <main class="flex-1 overflow-auto p-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-4 border-b border-gray-300 pb-2">
                <h2 class="text-base font-bold text-navy-800">{{ $title ?? 'Report' }}</h2>
                <div class="text-xs text-gray-500 no-print">Generated: {{ now()->format('d/M/Y H:i') }}</div>
            </div>
            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
