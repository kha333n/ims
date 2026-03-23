<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-64"><x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="All RMs" /></div>
            <div class="w-32"><label class="block text-xs font-medium text-gray-500 mb-1">Days Overdue</label><input wire:model="days_overdue" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Date</th><th class="px-2 py-2 text-right">Days</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-left">Phone</th><th class="px-2 py-2 text-left">SM</th><th class="px-2 py-2 text-left">RM</th><th class="px-2 py-2 text-left">Item</th><th class="px-2 py-2 text-right">Total</th><th class="px-2 py-2 text-right">Paid</th><th class="px-2 py-2 text-right">Remaining</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($defaulters as $d)
                    <tr><td class="px-2 py-1.5">{{ $d['id'] }}</td><td class="px-2 py-1.5">{{ formatDate($d['sale_date']) }}</td><td class="px-2 py-1.5 text-right font-bold text-red-600">{{ $d['days'] }}</td><td class="px-2 py-1.5">{{ $d['customer'] }}</td><td class="px-2 py-1.5">{{ $d['phone'] }}</td><td class="px-2 py-1.5">{{ $d['sale_man'] }}</td><td class="px-2 py-1.5">{{ $d['rm'] }}</td><td class="px-2 py-1.5">{{ $d['items'] }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($d['total'])</td><td class="px-2 py-1.5 text-right tabular-nums">@money($d['paid'])</td><td class="px-2 py-1.5 text-right tabular-nums font-bold text-red-600">@money($d['remaining'])</td></tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">No defaulters found.</td></tr>
                @endforelse
            </tbody>
            @if ($defaulters->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="8" class="px-2 py-2 text-right">Totals ({{ $defaulters->count() }} defaulters):</td><td class="px-2 py-2 text-right tabular-nums">@money($defaulters->sum('total'))</td><td class="px-2 py-2 text-right tabular-nums">@money($defaulters->sum('paid'))</td><td class="px-2 py-2 text-right tabular-nums text-red-600">@money($defaulters->sum('remaining'))</td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
