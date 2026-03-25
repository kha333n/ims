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
                        'permission' => 'products.view',
                        'items' => [
                            ['label' => 'Product List',  'route' => 'inventory.products'],
                            ['label' => 'Suppliers',     'route' => 'inventory.suppliers', 'permission' => 'suppliers.manage'],
                            ['label' => 'New Purchase',  'route' => 'inventory.purchase', 'permission' => 'purchases.manage'],
                        ],
                    ],
                    'Management' => [
                        'prefixes' => ['customers', 'sales', 'hr'],
                        'items' => [
                            ['label' => 'Customers',          'route' => 'customers.index', 'permission' => 'customers.view'],
                            ['label' => 'New Sale',           'route' => 'sales.new', 'permission' => 'sales.create'],
                            ['label' => 'Return Point',       'route' => 'sales.return', 'permission' => 'returns.manage'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Sale Men',           'route' => 'hr.sale-men', 'permission' => 'users.manage'],
                            ['label' => 'Recovery Men',       'route' => 'hr.recovery-men', 'permission' => 'users.manage'],
                            ['label' => 'Payroll',            'route' => 'hr.payroll', 'permission' => 'users.manage'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Daily Expenses',     'route' => 'expenses'],
                            ['label' => '---', 'route' => ''],
                            ['label' => 'Account Closure',    'route' => 'customers.closure', 'permission' => 'accounts.close'],
                            ['label' => 'Account Transfer',   'route' => 'customers.transfer', 'permission' => 'accounts.transfer'],
                            ['label' => 'Installment Update', 'route' => 'customers.installment-update', 'permission' => 'installments.update'],
                            ['label' => 'Problem Entry',      'route' => 'customers.problems', 'permission' => 'accounts.close'],
                        ],
                    ],
                    'Recovery' => [
                        'prefixes' => ['recovery'],
                        'permission' => 'recovery.entry',
                        'items' => [
                            ['label' => 'Recovery Entry', 'route' => 'recovery.entry'],
                        ],
                    ],
                    'Reports' => [
                        'prefixes' => ['reports'],
                        'permission' => 'reports.view',
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
                        'permission' => 'financial.view',
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
                        'permission' => 'settings.manage',
                        'items' => [
                            ['label' => 'Company Settings', 'route' => 'settings.index'],
                            ['label' => 'Backup & Restore', 'route' => 'settings.backup'],
                            ['label' => 'License',          'route' => 'settings.license'],
                            ['label' => 'User Management',  'route' => 'settings.users', 'permission' => 'users.manage'],
                        ],
                    ],
                ];
            @endphp

            @foreach ($menus as $label => $menu)
                @php
                    $menuPerm = $menu['permission'] ?? null;
                    $canSeeMenu = !$menuPerm || !auth()->check() || auth()->user()->can($menuPerm);
                    $isActive = collect($menu['prefixes'])->contains(fn($p) => str_starts_with($path, $p));
                @endphp
                @if ($canSeeMenu)
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
                            @elseif (!isset($item['permission']) || !auth()->check() || auth()->user()->can($item['permission']))
                                <a href="{{ route($item['route']) }}"
                                   class="block px-4 py-1.5 text-sm text-gray-300 hover:bg-navy-600 hover:text-white whitespace-nowrap">
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

            <div class="flex-1"></div>

            {{-- Clock --}}
            <div class="flex items-center px-3 text-xs text-gray-400 tabular-nums"
                 x-data="{ t: '' }"
                 x-init="setInterval(() => t = new Date().toLocaleTimeString('en-GB'), 1000)">
                <span x-text="t"></span>
            </div>

            {{-- Manual Link --}}
            @auth
                <a href="{{ route('manual') }}" class="flex items-center h-full px-2.5 text-xs text-gray-400 hover:bg-navy-700 hover:text-white transition-colors gap-1" title="User Manual">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                    <span>Manual</span>
                </a>
            @endauth

            {{-- User Menu --}}
            @auth
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button class="flex items-center h-full px-3 text-xs text-gray-300 hover:bg-navy-700 hover:text-white transition-colors gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ auth()->user()->name }}</span>
                    </button>
                    <div x-show="open" x-transition.opacity.duration.100ms
                         class="absolute right-0 top-full z-50 min-w-40 bg-navy-800 border border-navy-700 shadow-2xl py-1"
                         style="display:none;">
                        <a href="{{ route('profile') }}" class="block px-4 py-1.5 text-sm text-gray-300 hover:bg-navy-600 hover:text-white">Profile</a>
                        @can('users.manage')
                            <a href="{{ route('settings.users') }}" class="block px-4 py-1.5 text-sm text-gray-300 hover:bg-navy-600 hover:text-white">User Management</a>
                        @endcan
                        <div class="my-1 border-t border-navy-700"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-1.5 text-sm text-gray-300 hover:bg-navy-600 hover:text-white">Logout</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </nav>

    {{-- Quick Toolbar --}}
    <div class="no-print bg-toolbar shrink-0 flex items-center gap-1 px-2 py-1 border-b border-navy-900">
        @php
            $toolbar = [
                ['label' => 'New Purchases',  'route' => 'inventory.purchase', 'icon' => 'M12 4v16m8-8H4', 'permission' => 'purchases.manage'],
                ['label' => 'New Sales',       'route' => 'sales.new',          'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'permission' => 'sales.create'],
                ['label' => 'Recovery Entry',  'route' => 'recovery.entry',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'permission' => 'recovery.entry'],
                ['label' => 'New Customer',    'route' => 'customers.create',   'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'permission' => 'customers.manage'],
                ['label' => 'Expense Entry',  'route' => 'expenses',           'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z', 'permission' => 'settings.manage'],
            ];
        @endphp
        @foreach ($toolbar as $btn)
            @if (!auth()->check() || auth()->user()->can($btn['permission']))
            <a href="{{ route($btn['route']) }}"
               class="flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded
                      bg-navy-700 hover:bg-navy-500 text-gray-200 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $btn['icon'] }}"/>
                </svg>
                {{ $btn['label'] }}
            </a>
            @endif
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
