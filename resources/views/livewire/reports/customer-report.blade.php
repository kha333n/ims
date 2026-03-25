<div>
    {{-- Filter panel --}}
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        {{-- Mode toggle --}}
        <div class="flex gap-4 mb-3">
            <label class="flex items-center gap-2 cursor-pointer text-sm">
                <input type="radio" wire:model.live="filter_mode" value="account" class="text-navy-600">
                <span>Search by Account Number</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm">
                <input type="radio" wire:model.live="filter_mode" value="customer" class="text-navy-600">
                <span>Search by Customer</span>
            </label>
        </div>
        <div class="flex items-end gap-4">
            @if ($filter_mode === 'account')
                <div class="w-96">
                    <x-searchable-select wire-model="account_id" :options="$accOpts" label="Account Number" placeholder="Search account by ID or customer name..." :required="true" />
                </div>
            @else
                <div class="w-96">
                    <x-searchable-select wire-model="customer_id" :options="$customerOpts" label="Customer" placeholder="Search by ID or name..." :required="true" />
                </div>
            @endif
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                <svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generate
            </button>
        </div>
    </div>

    {{-- Single account view --}}
    @if ($generated && $account)
        <div class="bg-gray-50 rounded-lg px-5 py-4 mb-4">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Customer Information</h3>
            <dl class="grid grid-cols-3 gap-3 text-sm">
                <div><dt class="text-gray-500 text-xs">Customer ID (CID)</dt><dd class="font-bold">#{{ $account->customer->id }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Full Name</dt><dd class="font-medium">{{ $account->customer->name }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Father Name</dt><dd>{{ $account->customer->father_name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Phone</dt><dd>{{ $account->customer->mobile ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Address</dt><dd>{{ $account->customer->home_address ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">CNIC</dt><dd>{{ $account->customer->cnic ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="bg-gray-50 rounded-lg px-5 py-4 mb-4">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Account Details</h3>
            <dl class="grid grid-cols-3 gap-3 text-sm">
                <div><dt class="text-gray-500 text-xs">Account Number</dt><dd class="font-bold">#{{ $account->id }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Slip Number</dt><dd>{{ $account->slip_number ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Sale Date</dt><dd>{{ formatDate($account->sale_date) }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Sale Man</dt><dd>{{ $account->saleMan?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Recovery Man</dt><dd>{{ $account->recoveryMan?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs">Status</dt><dd><span class="px-2 py-0.5 text-xs rounded {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($account->status) }}</span></dd></div>
            </dl>
        </div>
        <h3 class="text-sm font-bold text-navy-800 mb-2">Items</h3>
        <table class="w-full text-xs border-collapse mb-4">
            <thead>
                <tr class="bg-navy-800 text-white">
                    <th class="px-2 py-2 text-left">Item</th>
                    <th class="px-2 py-2 text-right">Unit Price</th>
                    <th class="px-2 py-2 text-right">Quantity</th>
                    <th class="px-2 py-2 text-right">Subtotal</th>
                    <th class="px-2 py-2 text-center">Returned</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($account->items as $item)
                    <tr>
                        <td class="px-2 py-1.5">{{ $item->product?->name ?? '—' }}</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($item->unit_price)</td>
                        <td class="px-2 py-1.5 text-right">{{ $item->quantity }}</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($item->unit_price * $item->quantity)</td>
                        <td class="px-2 py-1.5 text-center">{{ $item->returned ? 'Yes' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="grid grid-cols-4 gap-4 bg-gray-50 rounded-lg px-5 py-3 text-sm mb-4">
            <div><span class="text-gray-500 text-xs block">Total Amount</span><span class="font-bold">@money($account->total_amount)</span></div>
            <div><span class="text-gray-500 text-xs block">Advance Paid</span><span>@money($account->advance_amount)</span></div>
            <div><span class="text-gray-500 text-xs block">Total Paid</span><span>@money($account->payments->sum('amount'))</span></div>
            <div><span class="text-gray-500 text-xs block">Remaining</span><span class="font-bold text-red-600">@money($account->remaining_amount)</span></div>
        </div>
        <h3 class="text-sm font-bold text-navy-800 mb-2">Payment History</h3>
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1 text-left">Date</th>
                    <th class="px-2 py-1 text-right">Amount</th>
                    <th class="px-2 py-1 text-left">Type</th>
                    <th class="px-2 py-1 text-left">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($account->payments->sortBy('payment_date') as $pay)
                    <tr>
                        <td class="px-2 py-1">{{ formatDate($pay->payment_date) }}</td>
                        <td class="px-2 py-1 text-right tabular-nums">@money($pay->amount)</td>
                        <td class="px-2 py-1">{{ ucfirst($pay->transaction_type) }}</td>
                        <td class="px-2 py-1">{{ $pay->remarks ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">No payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- Customer-based multi-account view --}}
    @if ($generated && $customer)
        {{-- Customer header --}}
        <div class="bg-navy-800 text-white rounded-lg px-5 py-4 mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wide text-navy-300 mb-2">Customer Profile</h3>
            <dl class="grid grid-cols-3 gap-3 text-sm">
                <div><dt class="text-navy-300 text-xs">Customer ID (CID)</dt><dd class="font-bold">#{{ $customer->id }}</dd></div>
                <div><dt class="text-navy-300 text-xs">Full Name</dt><dd class="font-medium">{{ $customer->name }}</dd></div>
                <div><dt class="text-navy-300 text-xs">Father Name</dt><dd>{{ $customer->father_name ?? '—' }}</dd></div>
                <div><dt class="text-navy-300 text-xs">Phone</dt><dd>{{ $customer->mobile ?? '—' }}</dd></div>
                <div><dt class="text-navy-300 text-xs">Address</dt><dd>{{ $customer->home_address ?? '—' }}</dd></div>
                <div><dt class="text-navy-300 text-xs">CNIC</dt><dd>{{ $customer->cnic ?? '—' }}</dd></div>
            </dl>
        </div>

        {{-- All accounts summary table --}}
        @if ($accounts->isEmpty())
            <p class="text-center text-gray-400 py-8">No accounts found for this customer.</p>
        @else
            <table class="w-full text-xs border-collapse mb-6">
                <thead>
                    <tr class="bg-navy-800 text-white">
                        <th class="px-2 py-2 text-left">Account #</th>
                        <th class="px-2 py-2 text-left">Slip #</th>
                        <th class="px-2 py-2 text-left">Sale Date</th>
                        <th class="px-2 py-2 text-left">Items</th>
                        <th class="px-2 py-2 text-right">Quantity</th>
                        <th class="px-2 py-2 text-right">Total Amount</th>
                        <th class="px-2 py-2 text-right">Advance</th>
                        <th class="px-2 py-2 text-right">Paid</th>
                        <th class="px-2 py-2 text-right">Remaining</th>
                        <th class="px-2 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($accounts as $acc)
                        <tr class="{{ $acc->status === 'active' ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="px-2 py-1.5 font-bold">#{{ $acc->id }}</td>
                            <td class="px-2 py-1.5">{{ $acc->slip_number ?? '—' }}</td>
                            <td class="px-2 py-1.5">{{ formatDate($acc->sale_date) }}</td>
                            <td class="px-2 py-1.5">{{ $acc->items->pluck('product.name')->filter()->join(', ') ?: '—' }}</td>
                            <td class="px-2 py-1.5 text-right">{{ $acc->items->sum('quantity') }}</td>
                            <td class="px-2 py-1.5 text-right tabular-nums">@money($acc->total_amount)</td>
                            <td class="px-2 py-1.5 text-right tabular-nums">@money($acc->advance_amount)</td>
                            <td class="px-2 py-1.5 text-right tabular-nums">@money($acc->payments->sum('amount'))</td>
                            <td class="px-2 py-1.5 text-right tabular-nums font-bold {{ $acc->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">@money($acc->remaining_amount)</td>
                            <td class="px-2 py-1.5 text-center"><span class="px-2 py-0.5 text-xs rounded {{ $acc->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($acc->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-bold text-xs">
                    <tr>
                        <td colspan="5" class="px-2 py-2 text-right">Totals ({{ $accounts->count() }} accounts):</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum('total_amount'))</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum('advance_amount'))</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($accounts->sum(fn($a) => $a->payments->sum('amount')))</td>
                        <td class="px-2 py-2 text-right tabular-nums text-red-600">@money($accounts->sum('remaining_amount'))</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            {{-- Per-account detail sections --}}
            @foreach ($accounts as $acc)
                <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 px-4 py-2 flex items-center justify-between">
                        <span class="font-bold text-sm text-navy-800">Account #{{ $acc->id }} — {{ formatDate($acc->sale_date) }}</span>
                        <span class="text-xs text-gray-500">Sale Man: {{ $acc->saleMan?->name ?? '—' }} &nbsp;|&nbsp; Recovery Man: {{ $acc->recoveryMan?->name ?? '—' }}</span>
                        <span class="px-2 py-0.5 text-xs rounded {{ $acc->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($acc->status) }}</span>
                    </div>
                    <div class="p-3">
                        <table class="w-full text-xs border-collapse mb-2">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-2 py-1 text-left">Item</th>
                                    <th class="px-2 py-1 text-right">Unit Price</th>
                                    <th class="px-2 py-1 text-right">Quantity</th>
                                    <th class="px-2 py-1 text-right">Subtotal</th>
                                    <th class="px-2 py-1 text-center">Returned</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($acc->items as $item)
                                    <tr>
                                        <td class="px-2 py-1">{{ $item->product?->name ?? '—' }}</td>
                                        <td class="px-2 py-1 text-right tabular-nums">@money($item->unit_price)</td>
                                        <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                                        <td class="px-2 py-1 text-right tabular-nums">@money($item->unit_price * $item->quantity)</td>
                                        <td class="px-2 py-1 text-center">{{ $item->returned ? 'Yes' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="flex gap-6 text-xs bg-gray-50 rounded px-3 py-2">
                            <span><span class="text-gray-500">Total:</span> <strong>@money($acc->total_amount)</strong></span>
                            <span><span class="text-gray-500">Advance:</span> @money($acc->advance_amount)</span>
                            <span><span class="text-gray-500">Paid:</span> @money($acc->payments->sum('amount'))</span>
                            <span><span class="text-gray-500">Remaining:</span> <strong class="{{ $acc->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">@money($acc->remaining_amount)</strong></span>
                            <span><span class="text-gray-500">Installment:</span> {{ ucfirst($acc->installment_type ?? '—') }} — @money($acc->installment_amount ?? 0)</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
</div>
