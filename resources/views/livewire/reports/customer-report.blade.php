<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-96"><x-searchable-select wire-model="account_id" :options="$accOpts" label="Account Number" placeholder="Search account by ID..." :required="true" /></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated && $account)
        <div class="bg-gray-50 rounded-lg px-5 py-4 mb-4">
            <dl class="grid grid-cols-3 gap-3 text-sm">
                <div><dt class="text-gray-500 text-xs">Account #</dt><dd class="font-bold">#{{ $account->id }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Slip #</dt><dd>{{ $account->slip_number ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Sale Date</dt><dd>{{ formatDate($account->sale_date) }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $account->customer->name }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Address</dt><dd>{{ $account->customer->home_address ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Phone</dt><dd>{{ $account->customer->mobile ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Sale Man</dt><dd>{{ $account->saleMan?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Recovery Man</dt><dd>{{ $account->recoveryMan?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Status</dt><dd><span class="px-2 py-0.5 text-xs rounded {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($account->status) }}</span></dd></div>
            </dl>
        </div>
        <h3 class="text-sm font-bold text-navy-800 mb-2">Items</h3>
        <table class="w-full text-xs border-collapse mb-4">
            <thead><tr class="bg-navy-800 text-white"><th class="px-2 py-2 text-left">Item</th><th class="px-2 py-2 text-right">Price</th><th class="px-2 py-2 text-right">Qty</th><th class="px-2 py-2 text-right">Subtotal</th><th class="px-2 py-2 text-center">Returned</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($account->items as $item)
                    <tr><td class="px-2 py-1.5">{{ $item->product?->name ?? '—' }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($item->unit_price)</td><td class="px-2 py-1.5 text-right">{{ $item->quantity }}</td><td class="px-2 py-1.5 text-right tabular-nums">@money($item->unit_price * $item->quantity)</td><td class="px-2 py-1.5 text-center">{{ $item->returned ? 'Yes' : '—' }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div class="grid grid-cols-4 gap-4 bg-gray-50 rounded-lg px-5 py-3 text-sm mb-4">
            <div><span class="text-gray-500 text-xs block">Total</span><span class="font-bold">@money($account->total_amount)</span></div>
            <div><span class="text-gray-500 text-xs block">Advance</span><span>@money($account->advance_amount)</span></div>
            <div><span class="text-gray-500 text-xs block">Paid</span><span>@money($account->payments->sum('amount'))</span></div>
            <div><span class="text-gray-500 text-xs block">Remaining</span><span class="font-bold text-red-600">@money($account->remaining_amount)</span></div>
        </div>
        <h3 class="text-sm font-bold text-navy-800 mb-2">Payment History</h3>
        <table class="w-full text-xs border-collapse">
            <thead><tr class="bg-gray-100"><th class="px-2 py-1 text-left">Date</th><th class="px-2 py-1 text-right">Amount</th><th class="px-2 py-1 text-left">Type</th><th class="px-2 py-1 text-left">Remarks</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($account->payments->sortBy('payment_date') as $pay)
                    <tr><td class="px-2 py-1">{{ formatDate($pay->payment_date) }}</td><td class="px-2 py-1 text-right tabular-nums">@money($pay->amount)</td><td class="px-2 py-1">{{ ucfirst($pay->transaction_type) }}</td><td class="px-2 py-1">{{ $pay->remarks ?? '—' }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
