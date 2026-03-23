<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Account Closure</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            {{-- Mode Toggle --}}
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input wire:model.live="mode" type="radio" value="close" class="text-navy-600 focus:ring-navy-400">
                    Close Account
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input wire:model.live="mode" type="radio" value="activate" class="text-navy-600 focus:ring-navy-400">
                    Activate Account
                </label>
            </div>

            {{-- RM Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recovery Man</label>
                <select wire:model.live="recovery_man_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    <option value="">— Select RM —</option>
                    @foreach ($recoveryMen as $rm)
                        <option value="{{ $rm->id }}">{{ $rm->name }}{{ $rm->area ? " ({$rm->area})" : '' }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Customer Selection --}}
            @if ($recovery_man_id)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select wire:model.live="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        <option value="">— Select Customer —</option>
                        @foreach ($customers as $cust)
                            <option value="{{ $cust->id }}">#{{ $cust->id }} — {{ $cust->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Account Selection --}}
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
            @endif

            {{-- Account Details --}}
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Discount Amount (PKR)</label>
                            <input wire:model="discount_amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            @error('discount_amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Discount Slip #</label>
                            <input wire:model="discount_slip" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button wire:click="closeAccount" class="px-5 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors">
                            Close Account
                        </button>
                    </div>
                @else
                    <div class="flex justify-end">
                        <button wire:click="activateAccount" class="px-5 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors">
                            Activate Account
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
