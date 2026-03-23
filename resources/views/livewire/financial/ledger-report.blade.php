<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-36"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-36"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-36"><label class="block text-xs font-medium text-gray-500 mb-1">Event Type</label><select wire:model="event_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"><option value="all">All</option><option value="sale">Sale</option><option value="payment">Payment</option><option value="recovery">Recovery</option><option value="return">Return</option><option value="closure">Closure</option><option value="activation">Activation</option><option value="purchase">Purchase</option><option value="loss">Loss</option></select></div>
            <div class="w-56"><x-searchable-select wire-model="customer_id" :options="$custOpts" label="Customer" placeholder="All customers" /></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated)
        @php $running = 0; @endphp
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Sr#</th><th class="px-2 py-2 text-left">Date/Time</th><th class="px-2 py-2 text-left">Type</th><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-left">Description</th><th class="px-2 py-2 text-right">Debit (In)</th><th class="px-2 py-2 text-right">Credit (Out)</th><th class="px-2 py-2 text-right">Balance</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($entries as $i => $e)
                    @php $running += $e->debit - $e->credit; @endphp
                    <tr><td class="px-2 py-1">{{ $i + 1 }}</td><td class="px-2 py-1">{{ $e->event_date->format('d/M/Y H:i') }}</td><td class="px-2 py-1 capitalize">{{ $e->event_type }}</td><td class="px-2 py-1">{{ $e->account_id ? '#'.$e->account_id : '—' }}</td><td class="px-2 py-1">{{ $e->customer?->name ?? '—' }}</td><td class="px-2 py-1">{{ $e->description }}</td><td class="px-2 py-1 text-right tabular-nums {{ $e->debit > 0 ? 'text-green-700 font-medium' : '' }}">{{ $e->debit > 0 ? formatMoney($e->debit) : '' }}</td><td class="px-2 py-1 text-right tabular-nums {{ $e->credit > 0 ? 'text-red-700 font-medium' : '' }}">{{ $e->credit > 0 ? formatMoney($e->credit) : '' }}</td><td class="px-2 py-1 text-right tabular-nums font-medium">{{ formatMoney(abs($running)) }}</td></tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No ledger entries found.</td></tr>
                @endforelse
            </tbody>
            @if ($entries->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="6" class="px-2 py-2 text-right">Totals:</td><td class="px-2 py-2 text-right tabular-nums text-green-700">@money($entries->sum('debit'))</td><td class="px-2 py-2 text-right tabular-nums text-red-700">@money($entries->sum('credit'))</td><td class="px-2 py-2 text-right tabular-nums">@money(abs($entries->sum('debit') - $entries->sum('credit')))</td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
