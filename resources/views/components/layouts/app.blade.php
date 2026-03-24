<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-content flex flex-col" x-data>

    {{-- Top Navigation Bar --}}
    <nav class="no-print bg-navy-800 text-white select-none shrink-0">
        <div class="flex items-stretch h-10">

            {{-- App Brand --}}
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 bg-navy-900 text-sm font-bold tracking-widest shrink-0 hover:bg-navy-800 transition-colors">
                IMS
            </a>

            {{-- Menus --}}
            @php
                $path = request()->path();
                $menus = [
                    'Items' => [
                        'prefixes' => ['inventory'],
                        'items' => [
                            ['label' => 'Product List',  'route' => 'inventory.products'],
                            ['label' => 'Suppliers',     'route' => 'inventory.suppliers'],
                            ['label' => 'New Purchase',  'route' => 'inventory.purchase'],
                        ],
                    ],
                    'Management' => [
                        'prefixes' => ['customers', 'sales', 'hr'],
                        'items' => [
                            ['label' => 'Customers',          'route' => 'customers.index'],
                            ['label' => 'New Sale',           'route' => 'sales.new'],
                            ['label' => 'Return Point',       'route' => 'sales.return'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Sale Men',           'route' => 'hr.sale-men'],
                            ['label' => 'Recovery Men',       'route' => 'hr.recovery-men'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Account Closure',    'route' => 'customers.closure'],
                            ['label' => 'Account Transfer',   'route' => 'customers.transfer'],
                            ['label' => 'Installment Update', 'route' => 'customers.installment-update'],
                            ['label' => 'Problem Entry',      'route' => 'customers.problems'],
                        ],
                    ],
                    'Recovery' => [
                        'prefixes' => ['recovery'],
                        'items' => [
                            ['label' => 'Recovery Entry', 'route' => 'recovery.entry'],
                        ],
                    ],
                    'Reports' => [
                        'prefixes' => ['reports'],
                        'items' => [
                            ['label' => 'Item Sale Report',   'route' => 'reports.item-sales'],
                            ['label' => 'Item Detail Report', 'route' => 'reports.item-detail'],
                            ['label' => 'Daily Recovery',     'route' => 'reports.daily-recovery'],
                            ['label' => 'Monthly Recovery',   'route' => 'reports.monthly-recovery'],
                            ['label' => 'Return Report',      'route' => 'reports.returns'],
                            ['label' => 'Salesman Report',    'route' => 'reports.salesman'],
                            ['label' => 'Inventory Status',   'route' => 'reports.inventory'],
                            ['label' => 'Customer Account',   'route' => 'reports.customer'],
                            ['label' => 'Defaulter Report',   'route' => 'reports.defaulters'],
                        ],
                    ],
                    'Financial' => [
                        'prefixes' => ['financial'],
                        'items' => [
                            ['label' => 'Daily Cash Book',       'route' => 'financial.cash-book'],
                            ['label' => 'Financial Ledger',      'route' => 'financial.ledger'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Profit & Loss',         'route' => 'financial.profit-loss'],
                            ['label' => 'Credit & Debit',        'route' => 'financial.credit-debit'],
                            ['label' => 'Collection Performance', 'route' => 'financial.collections'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Receivables (Aging)',   'route' => 'financial.receivables'],
                            ['label' => 'Losses & Write-offs',  'route' => 'financial.losses'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Supplier Expenses',     'route' => 'financial.supplier-expenses'],
                            ['label' => 'Commissions',           'route' => 'financial.commissions'],
                            ['label' => 'Inventory Valuation',   'route' => 'financial.inventory-valuation'],
                        ],
                    ],
                    'Settings' => [
                        'prefixes' => ['settings'],
                        'items' => [
                            ['label' => 'Company Settings', 'route' => 'settings.index'],
                            ['label' => 'Backup & Restore', 'route' => 'settings.backup'],
                            ['label' => 'License',          'route' => 'settings.license'],
                        ],
                    ],
                ];
            @endphp

            @foreach ($menus as $label => $menu)
                @php
                    $isActive = collect($menu['prefixes'])->contains(fn($p) => str_starts_with($path, $p));
                @endphp
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button class="flex items-center h-full px-4 text-sm font-medium transition-colors gap-1
                                   {{ $isActive ? 'bg-navy-600 text-white' : 'text-gray-200 hover:bg-navy-700 hover:text-white' }}">
                        {{ $label }}
                        <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-75"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute left-0 top-full z-50 min-w-48 bg-navy-800 border border-navy-700 shadow-2xl py-1"
                         style="display:none;">
                        @foreach ($menu['items'] as $item)
                            @if ($item['label'] === '---')
                                <div class="my-1 border-t border-navy-700"></div>
                            @else
                                <a href="{{ route($item['route']) }}"
                                   class="block px-4 py-1.5 text-sm text-gray-300 hover:bg-navy-600 hover:text-white whitespace-nowrap">
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex-1"></div>

            {{-- Clock --}}
            <div class="flex items-center px-4 text-xs text-gray-400 tabular-nums"
                 x-data="{ t: '' }"
                 x-init="setInterval(() => t = new Date().toLocaleTimeString('en-GB'), 1000)">
                <span x-text="t"></span>
            </div>
        </div>
    </nav>

    {{-- Quick Toolbar --}}
    <div class="no-print bg-toolbar shrink-0 flex items-center gap-1 px-2 py-1 border-b border-navy-900">
        @php
            $toolbar = [
                ['label' => 'New Purchases',  'route' => 'inventory.purchase', 'icon' => 'M12 4v16m8-8H4'],
                ['label' => 'New Sales',       'route' => 'sales.new',          'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ['label' => 'Recovery Entry',  'route' => 'recovery.entry',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'New Customer',    'route' => 'customers.create',   'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
            ];
        @endphp
        @foreach ($toolbar as $btn)
            <a href="{{ route($btn['route']) }}"
               class="flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded
                      bg-navy-700 hover:bg-navy-500 text-gray-200 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $btn['icon'] }}"/>
                </svg>
                {{ $btn['label'] }}
            </a>
        @endforeach
    </div>

{{-- Cloud Backup Warning Banner --}}
    @php try { $backupOverdue = app(\App\Services\BackupService::class)->shouldWarn(); } catch (\Throwable) { $backupOverdue = false; } @endphp
    @if ($backupOverdue)
        <div class="bg-yellow-400 text-black text-sm font-semibold px-4 py-2 flex items-center gap-2 no-print">
            <span class="text-lg">&#9888;</span>
            <span>Cloud backup overdue — connect to internet and <a href="{{ route('settings.backup') }}" class="underline font-bold hover:text-yellow-900">backup now</a></span>
        </div>
    @endif

    {{-- Main Content Area --}}
    <main class="flex-1 overflow-auto p-4">
        {{ $slot }}
    </main>

    {{-- Background auto-backup poller (every 5 min) --}}
    <livewire:system.backup-poller />

    @livewireScripts
</body>
</html>
