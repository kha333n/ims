<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-64"><x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="Search RM..." :required="true" /></div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50"><svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generate</button>
        </div>
    </div>
    @if ($generated)
        @if ($rmName)<p class="text-sm font-medium text-navy-800 mb-2">Recovery Man: {{ $rmName }}</p>@endif
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Sr#</th><th class="px-2 py-2 text-left">Date</th><th class="px-2 py-2 text-left">Acc#</th><th class="px-2 py-2 text-left">Customer</th><th class="px-2 py-2 text-right">Amount</th><th class="px-2 py-2 text-right">Remaining</th><th class="px-2 py-2 text-left">Type</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($payments as $i => $payment)
                    <tr><td class="px-2 py-1.5">{{ $i + 1 }}</td><td class="px-2 py-1.5">{{ formatDate($payment->payment_date) }}</td><td class="px-2 py-1.5">{{ $payment->account_id }}</td><td class="px-2 py-1.5">{{ $payment->account?->customer?->name ?? '—' }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($payment->amount)</td><td class="px-2 py-1.5 text-right tabular-nums">@money($payment->account?->remaining_amount ?? 0)</td><td class="px-2 py-1.5">{{ ucfirst($payment->transaction_type) }}</td></tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No recovery entries found.</td></tr>
                @endforelse
            </tbody>
            @if ($payments->count() > 0)
                <tfoot class="bg-gray-100 font-bold"><tr><td colspan="4" class="px-2 py-2 text-right">Total:</td><td class="px-2 py-2 text-right tabular-nums">@money($payments->sum('amount'))</td><td colspan="2"></td></tr></tfoot>
            @endif
        </table>
    @endif
</div>
