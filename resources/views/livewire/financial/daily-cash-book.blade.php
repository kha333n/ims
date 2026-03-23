<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-48"><label class="block text-xs font-medium text-gray-500 mb-1">Date</label><input wire:model.live="date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-6 mb-4">
        <div class="bg-green-50 rounded-lg px-4 py-3 text-center"><p class="text-xs text-gray-500">Opening Balance</p><p class="text-lg font-bold {{ $opening >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ formatMoney(abs($opening)) }}</p></div>
        <div class="rounded-lg px-4 py-3 text-center {{ $closing >= 0 ? 'bg-green-50' : 'bg-red-50' }}"><p class="text-xs text-gray-500">Closing Balance</p><p class="text-lg font-bold {{ $closing >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ formatMoney(abs($closing)) }}</p></div>
    </div>
    <div class="grid grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-bold text-green-700 mb-2">Receipts (Money In)</h3>
            <table class="w-full text-xs border-collapse"><thead><tr class="bg-green-100"><th class="px-2 py-1.5 text-left">Time</th><th class="px-2 py-1.5 text-left">Type</th><th class="px-2 py-1.5 text-left">Description</th><th class="px-2 py-1.5 text-right">Amount</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($receipts as $r)<tr><td class="px-2 py-1">{{ $r->event_date->format('H:i') }}</td><td class="px-2 py-1 capitalize">{{ $r->event_type }}</td><td class="px-2 py-1">{{ $r->description }}</td><td class="px-2 py-1 text-right tabular-nums text-green-700 font-medium">@money($r->debit)</td></tr>@empty<tr><td colspan="4" class="px-2 py-4 text-center text-gray-400">No receipts</td></tr>@endforelse
            </tbody><tfoot class="bg-green-50 font-bold"><tr><td colspan="3" class="px-2 py-1.5 text-right">Total:</td><td class="px-2 py-1.5 text-right tabular-nums text-green-700">@money($receipts->sum('debit'))</td></tr></tfoot></table>
        </div>
        <div>
            <h3 class="text-sm font-bold text-red-700 mb-2">Payments (Money Out)</h3>
            <table class="w-full text-xs border-collapse"><thead><tr class="bg-red-100"><th class="px-2 py-1.5 text-left">Time</th><th class="px-2 py-1.5 text-left">Type</th><th class="px-2 py-1.5 text-left">Description</th><th class="px-2 py-1.5 text-right">Amount</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($payments as $p)<tr><td class="px-2 py-1">{{ $p->event_date->format('H:i') }}</td><td class="px-2 py-1 capitalize">{{ $p->event_type }}</td><td class="px-2 py-1">{{ $p->description }}</td><td class="px-2 py-1 text-right tabular-nums text-red-700 font-medium">@money($p->credit)</td></tr>@empty<tr><td colspan="4" class="px-2 py-4 text-center text-gray-400">No payments</td></tr>@endforelse
            </tbody><tfoot class="bg-red-50 font-bold"><tr><td colspan="3" class="px-2 py-1.5 text-right">Total:</td><td class="px-2 py-1.5 text-right tabular-nums text-red-700">@money($payments->sum('credit'))</td></tr></tfoot></table>
        </div>
    </div>
</div>
