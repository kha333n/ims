<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Account Closure</h1>

        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 {{ $actionSummary['action'] === 'Closed' ? 'border-red-500' : 'border-green-500' }} px-6 py-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-bold {{ $actionSummary['action'] === 'Closed' ? 'text-red-700' : 'text-green-700' }}">Account {{ $actionSummary['action'] }}</h2>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-3 gap-2 text-sm">
                    <div><dt class="text-gray-500 text-xs">Account #</dt><dd class="font-bold">#{{ $actionSummary['account_id'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $actionSummary['customer'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Balance</dt><dd class="font-medium">{{ formatMoney($actionSummary['remaining']) }}</dd></div>
                    @if (isset($actionSummary['discount']) && $actionSummary['discount'] > 0)
                        <div><dt class="text-gray-500 text-xs">Discount</dt><dd>{{ formatMoney($actionSummary['discount']) }}</dd></div>
                    @endif
                </dl>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input wire:model.live="mode" type="radio" value="close" class="text-navy-600 focus:ring-navy-400"> Close Account
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input wire:model.live="mode" type="radio" value="activate" class="text-navy-600 focus:ring-navy-400"> Activate Account
                </label>
            </div>

            <x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="Search RM..." />

            @if ($recovery_man_id)
                <x-searchable-select wire-model="customer_id" :options="$custOpts" label="Customer" placeholder="Search customer..." />
            @endif

            @if ($customer_id)
                <x-searchable-select wire-model="account_id" :options="$accOpts" label="Account" placeholder="Search account..." />
            @endif

            @if ($accountInfo)
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <div><dt class="text-gray-500 text-xs">Name</dt><dd class="font-medium">{{ $accountInfo['name'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Contact</dt><dd>{{ $accountInfo['contact'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Address</dt><dd>{{ $accountInfo['address'] }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Status</dt><dd class="font-medium">{{ ucfirst($accountInfo['status']) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Total</dt><dd class="font-medium">{{ formatMoney($accountInfo['total']) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Collected</dt><dd>{{ formatMoney($accountInfo['paid']) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Balance</dt><dd class="font-medium text-red-600">{{ formatMoney($accountInfo['remaining']) }}</dd></div>
                    </dl>
                </div>

                @if ($mode === 'close')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-money-input wire-model="discount_amount" label="Discount Amount (PKR)" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Discount Slip #</label>
                            <input wire:model="discount_slip" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button wire:click="confirmClose" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="confirmClose" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Close Account</button>
                    </div>
                @else
                    <div class="flex justify-end">
                        <button wire:click="activateAccount" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="activateAccount" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Activate Account</button>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if ($showCloseConfirm)
        <x-confirm-dialog
            title="Close Account"
            message="Are you sure you want to close Account #{{ $account_id }}? This will mark it as closed{{ $accountInfo['remaining'] > 0 ? ' with a remaining balance of ' . formatMoney($accountInfo['remaining']) : '' }}."
            confirm-label="Yes, Close Account"
            wire-confirm="closeAccount"
            wire-cancel="cancelClose"
            variant="danger"
        />
    @endif
</div>
