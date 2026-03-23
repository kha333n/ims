<div>
    {{-- Top Stats Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Active Accounts</p>
            <p class="text-2xl font-bold text-navy-800 mt-1">{{ number_format($stats['active_accounts']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Receivables</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ formatMoney($stats['total_receivables']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Today's Collections</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ formatMoney($stats['today_collections']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Products in Stock</p>
            <p class="text-2xl font-bold text-navy-800 mt-1">{{ number_format($stats['total_stock']) }}</p>
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
                            <span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $diff >= 0 ? '+' : '' }}{{ formatMoney(abs($diff)) }}</span>
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
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-700">{{ $d['customer'] }} <span class="text-gray-400">({{ $d['days'] }}d)</span></span>
                            <span class="font-bold text-red-600">{{ formatMoney($d['remaining']) }}</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('reports.defaulters') }}" class="block mt-2 text-xs text-navy-600 hover:underline">View all defaulters</a>
            @else
                <p class="text-sm text-green-600">No defaulters!</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        {{-- Recent Payments --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Recent Payments</h2>
            <table class="w-full text-xs">
                <thead><tr class="text-gray-500"><th class="pb-1 text-left font-medium">Date</th><th class="pb-1 text-left font-medium">Customer</th><th class="pb-1 text-left font-medium">Type</th><th class="pb-1 text-right font-medium">Amount</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($recentPayments as $p)
                        <tr><td class="py-1">{{ formatDate($p['date']) }}</td><td class="py-1">{{ $p['customer'] }}</td><td class="py-1 capitalize">{{ $p['type'] }}</td><td class="py-1 text-right tabular-nums text-green-700 font-medium">{{ formatMoney($p['amount']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Recent Sales --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Recent Sales</h2>
            <table class="w-full text-xs">
                <thead><tr class="text-gray-500"><th class="pb-1 text-left font-medium">Acc#</th><th class="pb-1 text-left font-medium">Date</th><th class="pb-1 text-left font-medium">Customer</th><th class="pb-1 text-left font-medium">Items</th><th class="pb-1 text-right font-medium">Total</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($recentSales as $s)
                        <tr><td class="py-1 font-medium">#{{ $s['id'] }}</td><td class="py-1">{{ formatDate($s['date']) }}</td><td class="py-1">{{ $s['customer'] }}</td><td class="py-1 truncate max-w-24">{{ $s['items'] }}</td><td class="py-1 text-right tabular-nums font-medium">{{ formatMoney($s['total']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Recovery Performance --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Recovery Performance (This Month)</h2>
            <table class="w-full text-xs">
                <thead><tr class="text-gray-500"><th class="pb-1 text-left font-medium">RM</th><th class="pb-1 text-left font-medium">Area</th><th class="pb-1 text-right font-medium">Accounts</th><th class="pb-1 text-right font-medium">Collected</th><th class="pb-1 text-right font-medium">Pending</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($recovery as $r)
                        <tr><td class="py-1 font-medium">{{ $r['name'] }}</td><td class="py-1">{{ $r['area'] }}</td><td class="py-1 text-right">{{ $r['accounts'] }}</td><td class="py-1 text-right tabular-nums text-green-700">{{ formatMoney($r['collected']) }}</td><td class="py-1 text-right tabular-nums text-red-600">{{ formatMoney($r['pending']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Low Stock Alert --}}
        <div class="bg-white rounded-lg shadow px-5 py-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Low Stock Alert</h2>
            @if (count($lowStock) > 0)
                <div class="space-y-2">
                    @foreach ($lowStock as $p)
                        <div class="flex items-center justify-between text-sm px-3 py-2 rounded {{ $p['quantity'] === 0 ? 'bg-red-50' : 'bg-yellow-50' }}">
                            <span class="font-medium">{{ $p['name'] }}</span>
                            <span class="font-bold {{ $p['quantity'] === 0 ? 'text-red-600' : 'text-yellow-600' }}">{{ $p['quantity'] }} left</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('inventory.products') }}" class="block mt-2 text-xs text-navy-600 hover:underline">View all products</a>
            @else
                <p class="text-sm text-green-600">All products well stocked!</p>
            @endif
        </div>
    </div>
</div>
