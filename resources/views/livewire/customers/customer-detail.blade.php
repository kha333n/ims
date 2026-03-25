<div>
    <div class="max-w-6xl mx-auto">
        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700">{{ $actionSummary['action'] }}: {{ $actionSummary['detail'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-bold text-navy-800">Customer #{{ $customer->id }}</h1>
                <p class="text-sm text-gray-500">{{ $customer->name }}</p>
            </div>
            <a href="{{ route('customers.index') }}" class="px-3 py-1.5 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Back to List</a>
        </div>

        <div class="grid grid-cols-3 gap-6">
            {{-- Left: Customer Info --}}
            <div class="col-span-2 space-y-4">
                {{-- Customer Info Card --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-navy-800">Customer Information</h2>
                        @if (!$editing)
                            <button wire:click="startEdit" class="text-xs text-navy-500 hover:text-navy-700 font-medium">Edit</button>
                        @endif
                    </div>

                    @if ($editing)
                        <form wire:submit="saveCustomer" class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Name *</label>
                                    <input wire:model="name" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                    @error('name') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Father Name</label>
                                    <input wire:model="father_name" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Mobile</label>
                                    <input wire:model="mobile" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">CNIC</label>
                                    <input wire:model="cnic" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Reference</label>
                                <input wire:model="reference" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Home Address</label>
                                <input wire:model="home_address" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Shop Address</label>
                                <input wire:model="shop_address" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            </div>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" wire:click="cancelEdit" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded transition-colors">Cancel</button>
                                <button type="submit" wire:loading.attr="disabled" class="px-3 py-1.5 text-xs font-medium text-white bg-navy-600 hover:bg-navy-500 rounded transition-colors disabled:opacity-50"><svg wire:loading wire:target="saveCustomer" class="animate-spin -ml-1 mr-1 h-3 w-3 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save</button>
                            </div>
                        </form>
                    @else
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <div>
                                <dt class="text-gray-500 text-xs">Name</dt>
                                <dd class="font-medium">{{ $customer->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 text-xs">Father Name</dt>
                                <dd>{{ $customer->father_name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 text-xs">Mobile</dt>
                                <dd>{{ $customer->mobile ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 text-xs">CNIC</dt>
                                <dd>{{ $customer->cnic ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 text-xs">Reference</dt>
                                <dd>{{ $customer->reference ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 text-xs">Home Address</dt>
                                <dd>{{ $customer->home_address ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 text-xs">Shop Address</dt>
                                <dd>{{ $customer->shop_address ?? '—' }}</dd>
                            </div>
                        </dl>
                    @endif
                </div>

                {{-- Accounts Table --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-bold text-navy-800">Accounts</h2>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Acc#</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Item(s)</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-600">Total</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-600">Advance</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-600">Remaining</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-600">Status</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">RM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($accounts as $account)
                                <tr class="hover:bg-blue-50 transition-colors cursor-pointer {{ $expandedAccountId === $account->id ? 'bg-blue-50' : '' }}"
                                    wire:click="toggleAccount({{ $account->id }})" wire:key="acc-{{ $account->id }}">
                                    <td class="px-4 py-2 font-medium text-navy-800">
                                        <span class="text-gray-400 mr-1">{{ $expandedAccountId === $account->id ? '▼' : '▶' }}</span>
                                        {{ $account->id }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">
                                        {{ $account->items->pluck('product.name')->filter()->join(', ') ?: '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-right tabular-nums">@money($account->total_amount)</td>
                                    <td class="px-4 py-2 text-right tabular-nums">@money($account->advance_amount)</td>
                                    <td class="px-4 py-2 text-right tabular-nums font-medium {{ $account->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        @money($account->remaining_amount)
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $account->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-600">{{ $account->recoveryMan?->name ?? '—' }}</td>
                                </tr>

                                {{-- Expanded Account Detail --}}
                                @if ($expandedAccountId === $account->id)
                                    <tr wire:key="acc-detail-{{ $account->id }}">
                                        <td colspan="7" class="px-0 py-0 bg-gray-50">
                                            <div class="px-6 py-4 space-y-4">
                                                {{-- Account Info --}}
                                                <div class="grid grid-cols-4 gap-4 text-xs">
                                                    <div>
                                                        <span class="text-gray-500">Sale Date</span>
                                                        <p class="font-medium">@date($account->sale_date)</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Sale Man</span>
                                                        <p class="font-medium">{{ $account->saleMan?->name ?? '—' }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Recovery Man</span>
                                                        <p class="font-medium">{{ $account->recoveryMan?->name ?? '—' }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Installment</span>
                                                        <p class="font-medium">{{ ucfirst($account->installment_type ?? '—') }} — @money($account->installment_amount)</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Slip #</span>
                                                        <p class="font-medium">{{ $account->slip_number ?? '—' }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Discount</span>
                                                        <p class="font-medium">@money($account->discount_amount)</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Total Paid</span>
                                                        <p class="font-medium text-green-600">@money($account->total_paid)</p>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Remaining</span>
                                                        <p class="font-medium {{ $account->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">@money($account->remaining_amount)</p>
                                                    </div>
                                                </div>

                                                {{-- Items --}}
                                                <div>
                                                    <h4 class="text-xs font-bold text-navy-800 mb-2">Items</h4>
                                                    <table class="w-full text-xs">
                                                        <thead class="bg-white">
                                                            <tr class="border-b">
                                                                <th class="py-1.5 text-left font-medium text-gray-500">Product</th>
                                                                <th class="py-1.5 text-right font-medium text-gray-500">Qty</th>
                                                                <th class="py-1.5 text-right font-medium text-gray-500">Unit Price</th>
                                                                <th class="py-1.5 text-right font-medium text-gray-500">Subtotal</th>
                                                                <th class="py-1.5 text-center font-medium text-gray-500">Returned</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            @foreach ($account->items as $item)
                                                                <tr>
                                                                    <td class="py-1.5">{{ $item->product?->name ?? '—' }}</td>
                                                                    <td class="py-1.5 text-right tabular-nums">{{ $item->quantity }}</td>
                                                                    <td class="py-1.5 text-right tabular-nums">@money($item->unit_price)</td>
                                                                    <td class="py-1.5 text-right tabular-nums">@money($item->subtotal)</td>
                                                                    <td class="py-1.5 text-center">
                                                                        @if ($item->returned)
                                                                            <span class="text-red-500 font-medium">Yes</span>
                                                                        @else
                                                                            <span class="text-gray-400">No</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                {{-- Payment History --}}
                                                <div>
                                                    <h4 class="text-xs font-bold text-navy-800 mb-2">Payment History ({{ $account->payments->count() }})</h4>
                                                    @if ($account->payments->isNotEmpty())
                                                        <div class="max-h-60 overflow-y-auto">
                                                            <table class="w-full text-xs">
                                                                <thead class="bg-white sticky top-0">
                                                                    <tr class="border-b">
                                                                        <th class="py-1.5 text-left font-medium text-gray-500">Date</th>
                                                                        <th class="py-1.5 text-right font-medium text-gray-500">Amount</th>
                                                                        <th class="py-1.5 text-left font-medium text-gray-500">Type</th>
                                                                        <th class="py-1.5 text-left font-medium text-gray-500">Collected By</th>
                                                                        <th class="py-1.5 text-left font-medium text-gray-500">Remarks</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-gray-100">
                                                                    @foreach ($account->payments->sortByDesc('payment_date') as $payment)
                                                                        <tr>
                                                                            <td class="py-1.5">@date($payment->payment_date)</td>
                                                                            <td class="py-1.5 text-right tabular-nums font-medium text-green-600">@money($payment->amount)</td>
                                                                            <td class="py-1.5">
                                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium
                                                                                    {{ $payment->transaction_type === 'advance' ? 'bg-blue-100 text-blue-700' : ($payment->transaction_type === 'manual' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700') }}">
                                                                                    {{ ucfirst($payment->transaction_type) }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="py-1.5 text-gray-600">{{ $payment->collector?->name ?? '—' }}</td>
                                                                            <td class="py-1.5 text-gray-500">{{ $payment->remarks ?? '—' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <p class="text-xs text-gray-400">No payments recorded.</p>
                                                    @endif
                                                </div>

                                                {{-- Returns --}}
                                                @if ($account->returns->isNotEmpty())
                                                    <div>
                                                        <h4 class="text-xs font-bold text-red-700 mb-2">Returns ({{ $account->returns->count() }})</h4>
                                                        <table class="w-full text-xs">
                                                            <thead class="bg-white">
                                                                <tr class="border-b">
                                                                    <th class="py-1.5 text-left font-medium text-gray-500">Date</th>
                                                                    <th class="py-1.5 text-right font-medium text-gray-500">Qty</th>
                                                                    <th class="py-1.5 text-right font-medium text-gray-500">Amount</th>
                                                                    <th class="py-1.5 text-left font-medium text-gray-500">Reason</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100">
                                                                @foreach ($account->returns->sortByDesc('return_date') as $return)
                                                                    <tr>
                                                                        <td class="py-1.5">@date($return->return_date)</td>
                                                                        <td class="py-1.5 text-right tabular-nums">{{ $return->quantity }}</td>
                                                                        <td class="py-1.5 text-right tabular-nums text-red-600">@money($return->returning_amount)</td>
                                                                        <td class="py-1.5 text-gray-600">{{ $return->reason ?? '—' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right: Payment Entry + Summary --}}
            <div class="space-y-4">
                {{-- Balance Summary --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-2">Balance Summary</h2>
                    <div class="text-2xl font-bold tabular-nums {{ $customer->total_remaining > 0 ? 'text-red-600' : 'text-green-600' }}">
                        @money($customer->total_remaining)
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Total remaining across active accounts</p>
                </div>

                {{-- Quick Payment Entry --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Record Payment</h2>


                    @if ($accounts->where('status', 'active')->count() > 0)
                        <form wire:submit="savePayment" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Account</label>
                                <select wire:model="payment_account_id" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                    <option value="">— Select —</option>
                                    @foreach ($accounts->where('status', 'active') as $acc)
                                        <option value="{{ $acc->id }}">Acc# {{ $acc->id }} — @money($acc->remaining_amount) remaining</option>
                                    @endforeach
                                </select>
                                @error('payment_account_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-money-input wire-model="payment_amount" label="Amount (PKR)" class="py-1.5" />
                                @error('payment_amount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                                <select wire:model="transaction_type" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                    <option value="installment">Installment</option>
                                    <option value="advance">Advance</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                <input wire:model="payment_date" type="date" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                @error('payment_date') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Remarks</label>
                                <input wire:model="payment_remarks" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            </div>
                            <button type="submit" wire:loading.attr="disabled" class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors disabled:opacity-50">
                                <svg wire:loading wire:target="savePayment" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Record Payment
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-400">No active accounts to receive payments.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
