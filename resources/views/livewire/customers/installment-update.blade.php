<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Installment Plan Update</h1>

        @if ($updateSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-bold text-green-700">Plan Updated</h2>
                    <button wire:click="$set('updateSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-3 gap-2 text-sm">
                    <div><dt class="text-gray-500 text-xs">Account #</dt><dd class="font-bold">#{{ $updateSummary['account_id'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Old Plan</dt><dd>{{ $updateSummary['old_type'] }} — {{ formatMoney($updateSummary['old_amount']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">New Plan</dt><dd class="font-medium">{{ $updateSummary['new_type'] }} — {{ formatMoney($updateSummary['new_amount']) }}</dd></div>
                </dl>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            <x-searchable-select wire-model="customer_id" :options="$custOpts" label="Customer" placeholder="Search by ID or name..." />

            @if ($customer_id)
                <x-searchable-select wire-model="account_id" :options="$accOpts" label="Account" placeholder="Search account..." />
            @endif

            @if ($current_type)
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Current Plan</h3>
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <div><dt class="text-gray-500 text-xs">Type</dt><dd class="font-medium">{{ ucfirst($current_type) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Collection Day</dt><dd>{{ $current_day ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Amount per {{ rtrim($current_type, 'ly') }}</dt><dd class="font-medium">{{ formatMoney($current_amount) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Remaining Balance</dt><dd class="font-medium text-red-600">{{ formatMoney($remaining_amount) }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Mobile</dt><dd>{{ $customer_mobile }}</dd></div>
                        <div><dt class="text-gray-500 text-xs">Address</dt><dd>{{ $customer_address }}</dd></div>
                    </dl>
                </div>

                <h3 class="text-sm font-bold text-navy-800">New Plan</h3>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Installment Type</label>
                    <select wire:model.live="new_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @if ($new_type === 'weekly')
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Day of Week</label>
                            <select wire:model.live="new_day" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="">— Select —</option>
                                @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $i => $day)
                                    <option value="{{ $i + 1 }}">{{ $day }}</option>
                                @endforeach
                            </select>
                            @error('new_day') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @elseif ($new_type === 'monthly')
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Day of Month (1-31)</label>
                            <input wire:model.live="new_day" type="number" min="1" max="31" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            <p class="mt-0.5 text-xs text-gray-400">For shorter months, last day of month will be used</p>
                            @error('new_day') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div></div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Amount per {{ $new_type === 'daily' ? 'Day' : ($new_type === 'weekly' ? 'Week' : 'Month') }} (PKR)
                        </label>
                        <x-money-input wire-model="new_amount" :live="true" />
                        @error('new_amount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($this->periodsToComplete !== null)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-blue-800">Estimated completion: <strong>{{ $this->periodsToComplete }} {{ $this->periodLabel }}</strong> to pay off remaining {{ formatMoney($remaining_amount) }}</p>
                    </div>
                @endif

                <div class="flex justify-end">
                    <button wire:click="save" wire:loading.attr="disabled" class="px-5 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Update Plan</button>
                </div>
            @endif
        </div>
    </div>
</div>
