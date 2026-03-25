<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-32"><label class="block text-xs font-medium text-gray-500 mb-1">Group By</label><select wire:model="group_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"><option value="day">Day</option><option value="week">Week</option><option value="month">Month</option></select></div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50"><svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-3 py-2 text-left">Period</th><th class="px-3 py-2 text-right">Money In</th><th class="px-3 py-2 text-right">Money Out</th><th class="px-3 py-2 text-right">Net</th><th class="px-3 py-2 text-right">Cumulative</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($rows as $r)
                    <tr><td class="px-3 py-1.5 font-medium">{{ $r->period }}</td><td class="px-3 py-1.5 text-right tabular-nums text-green-700">@money($r->total_in ?? 0)</td><td class="px-3 py-1.5 text-right tabular-nums text-red-700">@money($r->total_out ?? 0)</td><td class="px-3 py-1.5 text-right tabular-nums font-bold {{ $r->net >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $r->net >= 0 ? '' : '-' }}@money(abs($r->net))</td><td class="px-3 py-1.5 text-right tabular-nums {{ $r->cumulative >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $r->cumulative >= 0 ? '' : '-' }}@money(abs($r->cumulative))</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td class="px-3 py-2 text-right">Totals:</td><td class="px-3 py-2 text-right tabular-nums text-green-700">@money($rows->sum('total_in'))</td><td class="px-3 py-2 text-right tabular-nums text-red-700">@money($rows->sum('total_out'))</td><td class="px-3 py-2 text-right tabular-nums">@money(abs($rows->sum('total_in') - $rows->sum('total_out')))</td><td></td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
