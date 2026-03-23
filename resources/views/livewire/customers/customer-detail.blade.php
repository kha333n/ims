<div>
    <div class="max-w-6xl mx-auto">
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
                                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-navy-600 hover:bg-navy-500 rounded transition-colors">Save</button>
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
                                <tr class="hover:bg-blue-50 transition-colors" wire:key="acc-{{ $account->id }}">
                                    <td class="px-4 py-2 font-medium text-navy-800">{{ $account->id }}</td>
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

                    @if (session()->has('payment_success'))
                        <div class="mb-3 px-3 py-2 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg">
                            {{ session('payment_success') }}
                        </div>
                    @endif

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
                                <label class="block text-xs font-medium text-gray-500 mb-1">Amount (PKR)</label>
                                <input wire:model="payment_amount" type="number" step="0.01" min="1" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none">
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
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors">
                                Record Payment
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
