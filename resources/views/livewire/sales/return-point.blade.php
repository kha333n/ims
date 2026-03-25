<div>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Return Point</h1>

        {{-- Summary --}}
        @if ($returnSummary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 border-orange-500 px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-orange-700">Return Processed</h2>
                    <button wire:click="$set('returnSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-3 gap-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $returnSummary['customer'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Account #</dt><dd class="font-bold">#{{ $returnSummary['account_id'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Item</dt><dd>{{ $returnSummary['item'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Amount</dt><dd class="font-bold text-red-600">{{ formatMoney($returnSummary['amount']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Action</dt><dd>{{ $returnSummary['action'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Reason</dt><dd>{{ $returnSummary['reason'] }}</dd></div>
                </dl>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="Search RM..." />
                <div wire:key="cust-select-{{ $recovery_man_id }}">
                    <x-searchable-select wire-model="customer_id" :options="$custOpts" label="Customer" placeholder="Search customer..." :disabled="!$recovery_man_id" />
                </div>
                <div wire:key="acc-select-{{ $customer_id }}">
                    <x-searchable-select wire-model="account_id" :options="$accOpts" label="Account #" placeholder="Search account..." :disabled="!$customer_id" />
                </div>
            </div>

            @if ($accountInfo)
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Account Details</h3>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $accountInfo['customer_name'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Phone</dt><dd>{{ $accountInfo['phone'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Slip #</dt><dd>{{ $accountInfo['slip'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Sale Man</dt><dd>{{ $accountInfo['sale_man'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Sale Date</dt><dd>{{ $accountInfo['sale_date'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Balance</dt><dd class="font-medium text-red-600">{{ formatMoney($accountInfo['remaining']) }}</dd></div>
                    </dl>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Select Item to Return</label>
                    <select wire:model.live="account_item_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        <option value="">— Select Item —</option>
                        @foreach ($accountInfo['items'] as $item)
                            @if (!$item['returned'])
                                <option value="{{ $item['id'] }}">{{ $item['name'] }} — {{ formatMoney($item['price']) }} x {{ $item['quantity'] }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('account_item_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                @if ($account_item_id)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-money-input wire-model="returning_amount" label="Returning Amount (PKR)" />
                            @error('returning_amount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Return Date</label>
                            <input wire:model="return_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Reason</label>
                        <textarea wire:model="reason" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Inventory Action</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input wire:model="inventory_action" type="radio" value="restock" class="text-navy-600 focus:ring-navy-400"> Restock
                            </label>
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input wire:model="inventory_action" type="radio" value="scrap" class="text-navy-600 focus:ring-navy-400"> Scrap
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button wire:click="confirmReturn" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="confirmReturn" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Process Return</button>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if ($showReturnConfirm)
        <x-confirm-dialog
            title="Confirm Return"
            message="Are you sure you want to process this return? The item will be {{ $inventory_action === 'restock' ? 'restocked to inventory' : 'scrapped' }}."
            confirm-label="Yes, Process Return"
            wire-confirm="processReturn"
            wire-cancel="cancelReturn"
            variant="warning"
        />
    @endif
</div>
