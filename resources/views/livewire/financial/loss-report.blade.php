<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-left">Phone</th><th class="px-2 py-2 text-left">Closed</th><th class="px-2 py-2 text-right">Total</th><th class="px-2 py-2 text-right">Paid</th><th class="px-2 py-2 text-right">Written Off</th><th class="px-2 py-2 text-right">Discount</th><th class="px-2 py-2 text-left">Slip#</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($losses as $a)
                    <tr class="bg-red-50"><td class="px-2 py-1.5 font-medium">{{ $a->id }}</td><td class="px-2 py-1.5">{{ $a->customer->name }}</td><td class="px-2 py-1.5">{{ $a->customer->mobile ?? '—' }}</td><td class="px-2 py-1.5">{{ formatDate($a->closed_at) }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($a->total_amount)</td><td class="px-2 py-1.5 text-right tabular-nums">@money($a->total_amount - $a->remaining_amount)</td><td class="px-2 py-1.5 text-right tabular-nums font-bold text-red-700">@money($a->remaining_amount)</td><td class="px-2 py-1.5 text-right tabular-nums">@money($a->discount_amount)</td><td class="px-2 py-1.5">{{ $a->discount_slip ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No losses found in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($losses->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="4" class="px-2 py-2 text-right">Totals ({{ $losses->count() }}):</td><td class="px-2 py-2 text-right tabular-nums">@money($losses->sum('total_amount'))</td><td class="px-2 py-2 text-right tabular-nums">@money($losses->sum('total_amount') - $losses->sum('remaining_amount'))</td><td class="px-2 py-2 text-right tabular-nums text-red-700">@money($losses->sum('remaining_amount'))</td><td class="px-2 py-2 text-right tabular-nums">@money($losses->sum('discount_amount'))</td><td></td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
