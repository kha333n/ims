<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-64"><x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="All RMs" /></div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50"><svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Recovery Man</th><th class="px-2 py-2 text-left">Area</th><th class="px-2 py-2 text-right">Accounts</th><th class="px-2 py-2 text-right">Expected</th><th class="px-2 py-2 text-right">Collected</th><th class="px-2 py-2 text-right">Shortfall</th><th class="px-2 py-2 text-right">Rate %</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($rows as $r)
                    <tr class="{{ $r['rate'] < 50 ? 'bg-red-50' : ($r['rate'] < 80 ? 'bg-yellow-50' : '') }}"><td class="px-2 py-1.5 font-medium">{{ $r['name'] }}</td><td class="px-2 py-1.5">{{ $r['area'] }}</td><td class="px-2 py-1.5 text-right">{{ $r['accounts'] }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($r['expected'])</td><td class="px-2 py-1.5 text-right tabular-nums text-green-700 font-medium">@money($r['collected'])</td><td class="px-2 py-1.5 text-right tabular-nums text-red-600">@money($r['shortfall'])</td><td class="px-2 py-1.5 text-right font-bold {{ $r['rate'] >= 80 ? 'text-green-700' : ($r['rate'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $r['rate'] }}%</td></tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No data.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="2" class="px-2 py-2 text-right">Totals:</td><td class="px-2 py-2 text-right">{{ $rows->sum('accounts') }}</td><td class="px-2 py-2 text-right tabular-nums">@money($rows->sum('expected'))</td><td class="px-2 py-2 text-right tabular-nums text-green-700">@money($rows->sum('collected'))</td><td class="px-2 py-2 text-right tabular-nums text-red-600">@money($rows->sum('shortfall'))</td><td class="px-2 py-2 text-right">{{ $rows->sum('expected') > 0 ? round(($rows->sum('collected') / $rows->sum('expected')) * 100, 1) : 0 }}%</td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
