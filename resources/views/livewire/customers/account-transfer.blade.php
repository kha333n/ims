<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Account Transfer</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            {{-- Customer Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select wire:model.live="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    <option value="">— Select Customer —</option>
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->id }}">#{{ $cust->id }} — {{ $cust->name }}</option>
                    @endforeach
                </select>
                @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Customer Info --}}
            @if ($customer_name)
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <dl class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-gray-500 text-xs">Customer</dt>
                            <dd class="font-medium">{{ $customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Active Accounts</dt>
                            <dd class="font-medium">{{ $active_account_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Total Remaining</dt>
                            <dd class="font-medium text-red-600">{{ formatMoney($total_remaining) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Current RM</dt>
                            <dd class="font-medium">{{ $current_rm_name }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Transfer To --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transfer To (New RM)</label>
                    <select wire:model="to_recovery_man_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        <option value="">— Select Recovery Man —</option>
                        @foreach ($recoveryMen as $rm)
                            <option value="{{ $rm->id }}" {{ $rm->id === $current_rm_id ? 'disabled' : '' }}>
                                {{ $rm->name }}{{ $rm->area ? " ({$rm->area})" : '' }}{{ $rm->id === $current_rm_id ? ' (current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_recovery_man_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none resize-none"></textarea>
                </div>

                <div class="flex justify-end">
                    <button wire:click="transfer" class="px-5 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">
                        Transfer Account
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
