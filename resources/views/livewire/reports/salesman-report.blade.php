<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-64"><x-searchable-select wire-model="sale_man_id" :options="$smOpts" label="Sale Man" placeholder="Search SM..." :required="true" /></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-32"><label class="block text-xs font-medium text-gray-500 mb-1">Status</label><select wire:model="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"><option value="all">All</option><option value="active">Active</option><option value="closed">Closed</option></select></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Slip#</th><th class="px-2 py-2 text-left">Date</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-left">Item</th><th class="px-2 py-2 text-right">Total</th><th class="px-2 py-2 text-right">Advance</th><th class="px-2 py-2 text-right">Remaining</th><th class="px-2 py-2 text-center">Status</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($accounts as $acc)
                    <tr><td class="px-2 py-1.5">{{ $acc->id }}</td><td class="px-2 py-1.5">{{ $acc->slip_number ?? '—' }}</td><td class="px-2 py-1.5">{{ formatDate($acc->sale_date) }}</td><td class="px-2 py-1.5">{{ $acc->customer->name }}</td><td class="px-2 py-1.5">{{ $acc->items->pluck('product.name')->filter()->join(', ') }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($acc->total_amount)</td><td class="px-2 py-1.5 text-right tabular-nums">@money($acc->advance_amount)</td><td class="px-2 py-1.5 text-right tabular-nums text-red-600">@money($acc->remaining_amount)</td><td class="px-2 py-1.5 text-center"><span class="px-1.5 py-0.5 text-xs rounded {{ $acc->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($acc->status) }}</span></td></tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No sales found.</td></tr>
                @endforelse
            </tbody>
            @if ($accounts->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="5" class="px-2 py-2 text-right">Totals:</td><td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum('total_amount'))</td><td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum('advance_amount'))</td><td class="px-2 py-2 text-right tabular-nums text-red-600">@money($accounts->sum('remaining_amount'))</td><td></td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
