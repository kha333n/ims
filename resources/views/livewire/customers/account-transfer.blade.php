<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Account Transfer</h1>

        @if ($transferSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-bold text-green-700">Transfer Complete</h2>
                    <button wire:click="$set('transferSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $transferSummary['customer'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Accounts Transferred</dt><dd class="font-bold">{{ $transferSummary['accounts'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">From RM</dt><dd>{{ $transferSummary['from_rm'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">To RM</dt><dd class="font-medium">{{ $transferSummary['to_rm'] }}</dd></div>
                </dl>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            <x-searchable-select wire-model="customer_id" :options="$custOpts" label="Customer" placeholder="Search by ID or name..." :required="true" />
            @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

            @if ($customer_name)
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $customer_name }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Active Accounts</dt><dd class="font-medium">{{ $active_account_count }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Total Remaining</dt><dd class="font-medium text-red-600">{{ formatMoney($total_remaining) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Current RM</dt><dd class="font-medium">{{ $current_rm_name }}</dd></div>
                    </dl>
                </div>

                <x-searchable-select wire-model="to_recovery_man_id" :options="$rmOpts" label="Transfer To (New RM)" placeholder="Search recovery man..." :required="true" />
                @error('to_recovery_man_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
                </div>

                <div class="flex justify-end">
                    <button wire:click="transfer" class="px-5 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Transfer Account</button>
                </div>
            @endif
        </div>
    </div>
</div>
