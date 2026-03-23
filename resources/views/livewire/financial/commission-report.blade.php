<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-64"><x-searchable-select wire-model="sale_man_id" :options="$smOpts" label="Sale Man" placeholder="All sale men" /></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse mb-4">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Sale Man</th><th class="px-2 py-2 text-right">Sales</th><th class="px-2 py-2 text-right">Total Amount</th><th class="px-2 py-2 text-right">Rate %</th><th class="px-2 py-2 text-right">Commission</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($rows as $r)<tr><td class="px-2 py-1.5 font-medium">{{ $r['name'] }}</td><td class="px-2 py-1.5 text-right">{{ $r['count'] }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($r['total'])</td><td class="px-2 py-1.5 text-right">{{ $r['percent'] }}%</td><td class="px-2 py-1.5 text-right tabular-nums font-bold text-green-700">@money($r['commission'])</td></tr>@empty<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No sales found.</td></tr>@endforelse
            </tbody>
            @if ($rows->count() > 0)<tfoot class="bg-gray-100 font-bold"><tr><td class="px-2 py-2 text-right">Totals:</td><td class="px-2 py-2 text-right">{{ $rows->sum('count') }}</td><td class="px-2 py-2 text-right tabular-nums">@money($rows->sum('total'))</td><td></td><td class="px-2 py-2 text-right tabular-nums text-green-700">@money($rows->sum('commission'))</td></tr></tfoot>@endif
        </table>
        @if ($detail->count() > 0)
            <h3 class="text-sm font-bold text-navy-800 mb-2">Sale Details</h3>
            <table class="w-full text-xs border-collapse">
                <thead><tr class="bg-gray-100"><th class="px-2 py-1 text-left">Acc#</th><th class="px-2 py-1 text-left">Date</th><th class="px-2 py-1 text-left">Customer</th><th class="px-2 py-1 text-right">Total</th><th class="px-2 py-1 text-right">Commission</th></tr></thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($detail as $d)
                        @php $sm = \App\Models\Employee::find($sale_man_id); $comm = (int)($d->total_amount * (($sm?->commission_percent ?? 0) / 100)); @endphp
                        <tr><td class="px-2 py-1">{{ $d->id }}</td><td class="px-2 py-1">{{ formatDate($d->sale_date) }}</td><td class="px-2 py-1">{{ $d->customer->name }}</td><td class="px-2 py-1 text-right tabular-nums">@money($d->total_amount)</td><td class="px-2 py-1 text-right tabular-nums text-green-700">@money($comm)</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</div>
