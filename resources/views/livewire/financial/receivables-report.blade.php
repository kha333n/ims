<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-64"><x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="All RMs" /></div>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-4 mb-4">
        @foreach (['0-30' => 'green', '31-60' => 'yellow', '61-90' => 'orange', '90+' => 'red'] as $bucket => $color)
            <div class="bg-{{ $color }}-50 rounded-lg px-4 py-3 text-center border border-{{ $color }}-200">
                <p class="text-xs text-gray-500">{{ $bucket }} days</p>
                <p class="text-lg font-bold text-{{ $color }}-700">{{ formatMoney($buckets[$bucket]) }}</p>
                <p class="text-xs text-gray-400">{{ $bucketCounts[$bucket] }} accounts</p>
            </div>
        @endforeach
    </div>
    <table class="w-full text-xs border-collapse">
        <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-left">Phone</th><th class="px-2 py-2 text-left">RM</th><th class="px-2 py-2 text-left">Sale Date</th><th class="px-2 py-2 text-right">Days</th><th class="px-2 py-2 text-right">Total</th><th class="px-2 py-2 text-right">Paid</th><th class="px-2 py-2 text-right">Remaining</th><th class="px-2 py-2 text-center">Aging</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($accounts as $a)
                <tr class="{{ $a['bucket'] === '90+' ? 'bg-red-50' : ($a['bucket'] === '61-90' ? 'bg-orange-50' : '') }}"><td class="px-2 py-1.5">{{ $a['id'] }}</td><td class="px-2 py-1.5">{{ $a['customer'] }}</td><td class="px-2 py-1.5">{{ $a['phone'] }}</td><td class="px-2 py-1.5">{{ $a['rm'] }}</td><td class="px-2 py-1.5">{{ formatDate($a['sale_date']) }}</td><td class="px-2 py-1.5 text-right font-bold">{{ $a['days'] }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($a['total'])</td><td class="px-2 py-1.5 text-right tabular-nums">@money($a['paid'])</td><td class="px-2 py-1.5 text-right tabular-nums font-bold text-red-600">@money($a['remaining'])</td><td class="px-2 py-1.5 text-center"><span class="px-1.5 py-0.5 text-xs rounded bg-gray-100">{{ $a['bucket'] }}</span></td></tr>
            @empty
                <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">No outstanding receivables.</td></tr>
            @endforelse
        </tbody>
        @if ($accounts->count() > 0)
            <tfoot class="bg-gray-100 font-bold"><tr><td colspan="6" class="px-2 py-2 text-right">Totals ({{ $accounts->count() }}):</td><td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum('total'))</td><td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum('paid'))</td><td class="px-2 py-2 text-right tabular-nums text-red-600">@money($accounts->sum('remaining'))</td><td></td></tr></tfoot>
        @endif
    </table>
</div>
