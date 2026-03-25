<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50"><svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generate</button>
        </div>
    </div>
    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Sr#</th><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Date</th><th class="px-2 py-2 text-left">RM</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-left">Item</th><th class="px-2 py-2 text-right">Qty</th><th class="px-2 py-2 text-right">Amount</th><th class="px-2 py-2 text-left">Reason</th><th class="px-2 py-2 text-left">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($returns as $i => $ret)
                    <tr><td class="px-2 py-1.5">{{ $i + 1 }}</td><td class="px-2 py-1.5">{{ $ret->account_id }}</td><td class="px-2 py-1.5">{{ formatDate($ret->return_date) }}</td><td class="px-2 py-1.5">{{ $ret->account?->recoveryMan?->name ?? '—' }}</td><td class="px-2 py-1.5">{{ $ret->account?->customer?->name ?? '—' }}</td><td class="px-2 py-1.5">{{ $ret->accountItem?->product?->name ?? '—' }}</td><td class="px-2 py-1.5 text-right">{{ $ret->quantity }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($ret->returning_amount)</td><td class="px-2 py-1.5">{{ $ret->reason ?? '—' }}</td><td class="px-2 py-1.5">{{ ucfirst($ret->inventory_action) }}</td></tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">No returns found.</td></tr>
                @endforelse
            </tbody>
            @if ($returns->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="7" class="px-2 py-2 text-right">Total:</td><td class="px-2 py-2 text-right tabular-nums">@money($returns->sum('returning_amount'))</td><td colspan="2"></td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
