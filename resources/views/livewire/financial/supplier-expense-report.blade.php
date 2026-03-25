<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-64"><x-searchable-select wire-model="supplier_id" :options="$supplierOpts" label="Supplier" placeholder="All suppliers" /></div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50"><svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generate</button>
        </div>
    </div>
    @if ($generated)
        @php $grandTotal = 0; @endphp
        @forelse ($groups as $supplierName => $purchases)
            @php $subtotal = $purchases->sum(fn($p) => $p->unit_cost * $p->quantity); $grandTotal += $subtotal; @endphp
            <h3 class="text-sm font-bold text-navy-800 mt-4 mb-1">{{ $supplierName }} — Total: {{ formatMoney($subtotal) }}</h3>
            <table class="w-full text-xs border-collapse mb-2">
                <thead><tr class="bg-gray-100"><th class="px-2 py-1 text-left">Product</th><th class="px-2 py-1 text-right">Qty</th><th class="px-2 py-1 text-right">Unit Cost</th><th class="px-2 py-1 text-right">Total</th><th class="px-2 py-1 text-left">Date</th></tr></thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($purchases as $p)<tr><td class="px-2 py-1">{{ $p->product?->name ?? '—' }}</td><td class="px-2 py-1 text-right">{{ $p->quantity }}</td><td class="px-2 py-1 text-right tabular-nums">@money($p->unit_cost)</td><td class="px-2 py-1 text-right tabular-nums">@money($p->unit_cost * $p->quantity)</td><td class="px-2 py-1">{{ formatDate($p->purchase_date) }}</td></tr>@endforeach
                </tbody>
            </table>
        @empty
            <p class="text-center text-gray-400 py-8">No purchases found.</p>
        @endforelse
        @if ($grandTotal > 0)<div class="mt-4 text-right text-sm font-bold">Grand Total: <span class="text-red-600">{{ formatMoney($grandTotal) }}</span></div>@endif
    @endif
</div>
