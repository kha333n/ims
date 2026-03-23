<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Installment Plan Update</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select wire:model.live="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    <option value="">— Select Customer —</option>
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->id }}">#{{ $cust->id }} — {{ $cust->name }}</option>
                    @endforeach
                </select>
            </div>

            @if ($customer_id && $accounts->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                    <select wire:model.live="account_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        <option value="">— Select Account —</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">Acc# {{ $acc->id }} — {{ formatMoney($acc->remaining_amount) }} remaining</option>
                        @endforeach
                    </select>
                    @error('account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @elseif ($customer_id)
                <p class="text-sm text-gray-400">No active accounts for this customer.</p>
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

                {{-- Type --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Installment Type</label>
                    <select wire:model.live="new_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                    @error('new_type') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Day (conditional) --}}
                    @if ($new_type === 'weekly')
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Day of Week</label>
                            <select wire:model.live="new_day" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="">— Select —</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="7">Sunday</option>
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

                    {{-- Amount --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Amount per {{ $new_type === 'daily' ? 'Day' : ($new_type === 'weekly' ? 'Week' : 'Month') }} (PKR)
                        </label>
                        <input wire:model.live.debounce.300ms="new_amount" type="number" step="0.01" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        @error('new_amount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Calculated completion --}}
                @if ($this->periodsToComplete !== null)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-blue-800">
                            Estimated completion: <strong>{{ $this->periodsToComplete }} {{ $this->periodLabel }}</strong>
                            to pay off remaining {{ formatMoney($remaining_amount) }}
                        </p>
                    </div>
                @endif

                <div class="flex justify-end">
                    <button wire:click="save" class="px-5 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">
                        Update Plan
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
