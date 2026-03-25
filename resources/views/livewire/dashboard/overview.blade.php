<div>
    {{-- Top Stats Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full bg-navy-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-navy-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Active Accounts</p>
                <p class="text-2xl font-bold text-navy-800">{{ number_format($stats['active_accounts']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Receivables</p>
                <p class="text-2xl font-bold text-red-600">{{ formatMoney($stats['total_receivables']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Today's Collections</p>
                <p class="text-2xl font-bold text-green-700">{{ formatMoney($stats['today_collections']) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Products in Stock</p>
                <p class="text-2xl font-bold text-navy-800">{{ number_format($stats['total_stock']) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6 mb-6">
        {{-- Monthly Comparison --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">This Month vs Last Month</h2>
            <div class="space-y-3 text-sm">
                @php
                    $fields = [
                        'Sales' => ['sales', 'sales'],
                        'Collections' => ['collections', 'collections'],
                        'Purchases' => ['purchases', 'purchases'],
                    ];
                @endphp
                @foreach ($fields as $label => [$thisKey, $lastKey])
                    @php
                        $this_val = $monthly['this_month'][$thisKey];
                        $last_val = $monthly['last_month'][$lastKey];
                        $diff = $this_val - $last_val;
                    @endphp
                    <div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-bold">{{ formatMoney($this_val) }}</span>
                        </div>
                        <div class="flex justify-between text-xs mt-0.5">
                            <span class="text-gray-400">Last: {{ formatMoney($last_val) }}</span>
                            <span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                @if ($diff >= 0) <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                @else <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @endif
                                {{ formatMoney(abs($diff)) }}
                            </span>
                        </div>
                    </div>
                @endforeach
                <div class="pt-2 border-t">
                    <div class="flex justify-between">
                        <span class="text-gray-600">New Sales</span>
                        <span class="font-bold">{{ $monthly['this_month']['sales_count'] }}</span>
                    </div>
                    <div class="text-xs text-gray-400">Last month: {{ $monthly['last_month']['sales_count'] }}</div>
                </div>
            </div>
        </div>

        {{-- Profit Overview --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Profit This Month</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-600">Revenue</span><span class="font-medium">{{ formatMoney($profit['revenue']) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">COGS</span><span class="text-red-600">- {{ formatMoney($profit['cogs']) }}</span></div>
                <div class="flex justify-between font-bold border-t pt-2"><span>Gross Profit</span><span class="{{ $profit['gross'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ formatMoney(abs($profit['gross'])) }}</span></div>
                @if ($profit['discounts'] > 0)<div class="flex justify-between text-xs"><span class="text-gray-500">Discounts</span><span class="text-red-500">- {{ formatMoney($profit['discounts']) }}</span></div>@endif
                @if ($profit['losses'] > 0)<div class="flex justify-between text-xs"><span class="text-gray-500">Losses</span><span class="text-red-500">- {{ formatMoney($profit['losses']) }}</span></div>@endif
                <div class="flex justify-between font-bold text-lg border-t pt-2"><span>Net Profit</span><span class="{{ $profit['net'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ formatMoney(abs($profit['net'])) }}</span></div>
            </div>
            <a href="{{ route('financial.profit-loss') }}" class="block mt-3 text-xs text-navy-600 hover:underline">View full P&L report</a>
        </div>

        {{-- Defaulter Alert --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Defaulters ({{ $defaulters['days_threshold'] }}+ days)</h2>
            <div class="flex items-center gap-4 mb-3">
                <div class="text-center">
                    <p class="text-2xl font-bold {{ $defaulters['count'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $defaulters['count'] }}</p>
                    <p class="text-xs text-gray-400">Accounts</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-red-600">{{ formatMoney($defaulters['total']) }}</p>
                    <p class="text-xs text-gray-400">Total Overdue</p>
                </div>
            </div>
            @if (count($defaulters['top']) > 0)
                <div class="space-y-1.5">
                    @foreach ($defaulters['top'] as $d)
                        <div class="flex items-center justify-between text-xs px-2 py-1.5 rounded bg-red-50">
                            <span class="text-gray-700 truncate">{{ $d['customer'] }} <span class="text-gray-400">({{ $d['days'] }}d)</span></span>
                            <span class="font-bold text-red-600 shrink-0 ml-2">{{ formatMoney($d['remaining']) }}</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('reports.defaulters') }}" class="block mt-2 text-xs text-navy-600 hover:underline">View all defaulters</a>
            @else
                <div class="text-center py-4">
                    <svg class="w-8 h-8 mx-auto text-green-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-600 font-medium">No defaulters!</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        {{-- Recent Payments --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-navy-800">Recent Payments</h2>
                <a href="{{ route('financial.ledger') }}" class="text-xs text-navy-600 hover:underline">View ledger</a>
            </div>
            @if (count($recentPayments) > 0)
                <table class="w-full text-xs">
                    <thead><tr class="text-gray-500"><th class="pb-1 text-left font-medium">Date</th><th class="pb-1 text-left font-medium">Customer</th><th class="pb-1 text-left font-medium">Type</th><th class="pb-1 text-right font-medium">Amount</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentPayments as $p)
                            <tr><td class="py-1">{{ formatDate($p['date']) }}</td><td class="py-1 truncate max-w-28">{{ $p['customer'] }}</td><td class="py-1 capitalize">{{ $p['type'] }}</td><td class="py-1 text-right tabular-nums text-green-700 font-medium">{{ formatMoney($p['amount']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-6">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-gray-400">No payments recorded yet.</p>
                </div>
            @endif
        </div>

        {{-- Recent Sales --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-navy-800">Recent Sales</h2>
                <a href="{{ route('sales.new') }}" class="text-xs text-navy-600 hover:underline">New sale</a>
            </div>
            @if (count($recentSales) > 0)
                <table class="w-full text-xs">
                    <thead><tr class="text-gray-500"><th class="pb-1 text-left font-medium">Acc#</th><th class="pb-1 text-left font-medium">Date</th><th class="pb-1 text-left font-medium">Customer</th><th class="pb-1 text-left font-medium">Items</th><th class="pb-1 text-right font-medium">Total</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentSales as $s)
                            <tr><td class="py-1 font-medium">#{{ $s['id'] }}</td><td class="py-1">{{ formatDate($s['date']) }}</td><td class="py-1 truncate max-w-24">{{ $s['customer'] }}</td><td class="py-1 truncate max-w-24">{{ $s['items'] }}</td><td class="py-1 text-right tabular-nums font-medium">{{ formatMoney($s['total']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-6">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="text-sm text-gray-400">No sales recorded yet.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Recovery Performance --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-navy-800">Recovery Performance (This Month)</h2>
                <a href="{{ route('recovery.entry') }}" class="text-xs text-navy-600 hover:underline">Recovery entry</a>
            </div>
            @if (count($recovery) > 0)
                <table class="w-full text-xs">
                    <thead><tr class="text-gray-500"><th class="pb-1 text-left font-medium">RM</th><th class="pb-1 text-left font-medium">Area</th><th class="pb-1 text-right font-medium">Accounts</th><th class="pb-1 text-right font-medium">Collected</th><th class="pb-1 text-right font-medium">Pending</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recovery as $r)
                            <tr><td class="py-1 font-medium">{{ $r['name'] }}</td><td class="py-1">{{ $r['area'] }}</td><td class="py-1 text-right">{{ $r['accounts'] }}</td><td class="py-1 text-right tabular-nums text-green-700">{{ formatMoney($r['collected']) }}</td><td class="py-1 text-right tabular-nums text-red-600">{{ formatMoney($r['pending']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-6">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-sm text-gray-400">No recovery men assigned yet.</p>
                </div>
            @endif
        </div>

        {{-- Low Stock Alert --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-navy-800">Low Stock Alert</h2>
                <a href="{{ route('inventory.products') }}" class="text-xs text-navy-600 hover:underline">All products</a>
            </div>
            @if (count($lowStock) > 0)
                <div class="space-y-2">
                    @foreach ($lowStock as $p)
                        <div class="flex items-center justify-between text-sm px-3 py-2 rounded {{ $p['quantity'] === 0 ? 'bg-red-50' : 'bg-yellow-50' }}">
                            <span class="font-medium">{{ $p['name'] }}</span>
                            <span class="font-bold {{ $p['quantity'] === 0 ? 'text-red-600' : 'text-yellow-600' }}">{{ $p['quantity'] }} left</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-8 h-8 mx-auto text-green-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-600 font-medium">All products well stocked!</p>
                </div>
            @endif
        </div>
    </div>
</div>
