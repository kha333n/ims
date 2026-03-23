<div>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">New Sale</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            {{-- Left: Customer Panel --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Customer</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Party ID</label>
                            <select wire:model.live="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="">— Select Customer —</option>
                                @foreach ($customers as $cust)
                                    <option value="{{ $cust->id }}">#{{ $cust->id }} — {{ $cust->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if ($customer_name)
                            <dl class="grid grid-cols-2 gap-2 text-sm bg-gray-50 rounded-lg px-3 py-2">
                                <div><dt class="text-gray-500 text-xs">Name</dt><dd class="font-medium">{{ $customer_name }}</dd></div>
                                <div><dt class="text-gray-500 text-xs">Father</dt><dd>{{ $customer_father ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500 text-xs">Mobile</dt><dd>{{ $customer_mobile ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500 text-xs">Reference</dt><dd>{{ $customer_reference ?? '—' }}</dd></div>
                                <div class="col-span-2"><dt class="text-gray-500 text-xs">Address</dt><dd>{{ $customer_address ?? '—' }}</dd></div>
                            </dl>
                        @endif

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Sale Date</label>
                                <input wire:model="sale_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                @error('sale_date') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Slip #</label>
                                <input wire:model="slip_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Installment Plan --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Installment Plan</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                            <select wire:model.live="installment_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            @if ($installment_type === 'weekly')
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Day of Week</label>
                                    <select wire:model="installment_day" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                        <option value="">— Select —</option>
                                        @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $i => $day)
                                            <option value="{{ $i + 1 }}">{{ $day }}</option>
                                        @endforeach
                                    </select>
                                    @error('installment_day') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @elseif ($installment_type === 'monthly')
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Day of Month</label>
                                    <input wire:model="installment_day" type="number" min="1" max="31" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                    @error('installment_day') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div></div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Installment Amount (PKR)</label>
                                <input wire:model.live.debounce.300ms="installment_amount" type="number" step="0.01" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                @error('installment_amount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        @if ($this->totalInstallments !== null)
                            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                                <p class="text-xs text-blue-800">
                                    Estimated: <strong>{{ $this->totalInstallments }} {{ $this->periodLabel }}</strong> to complete
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Staff Assignment --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Staff Assignment</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Sale Man</label>
                            <select wire:model="sale_man_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="">— Select —</option>
                                @foreach ($saleMen as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                @endforeach
                            </select>
                            @error('sale_man_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Recovery Man</label>
                            <select wire:model="recovery_man_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="">— Select —</option>
                                @foreach ($recoveryMen as $rm)
                                    <option value="{{ $rm->id }}">{{ $rm->name }}{{ $rm->area ? " ({$rm->area})" : '' }}</option>
                                @endforeach
                            </select>
                            @error('recovery_man_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Items & Financial --}}
            <div class="space-y-4">
                {{-- Add Item --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Items</h2>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Product</label>
                            <select wire:model.live="selected_product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="">— Select —</option>
                                @foreach ($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->quantity }} in stock)</option>
                                @endforeach
                            </select>
                            @error('selected_product_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-28">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Price</label>
                            <input wire:model="item_price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                        <div class="w-20">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Qty</label>
                            <input wire:model="item_quantity" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            @error('item_quantity') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="addItem" class="px-3 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm rounded-lg transition-colors">+ Add</button>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-navy-800 text-white">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Item</th>
                                <th class="px-4 py-2 text-right font-medium">Price</th>
                                <th class="px-4 py-2 text-right font-medium">Qty</th>
                                <th class="px-4 py-2 text-right font-medium">Subtotal</th>
                                <th class="px-4 py-2 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $index => $item)
                                <tr wire:key="sale-item-{{ $index }}">
                                    <td class="px-4 py-2 font-medium text-navy-800">{{ $item['name'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">PKR {{ number_format((float) $item['price'], 0) }}</td>
                                    <td class="px-4 py-2 text-right">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">PKR {{ number_format((float) $item['price'] * $item['quantity'], 0) }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No items added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @error('items') <p class="px-4 py-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Financial Summary --}}
                @if (count($items) > 0)
                    <div class="bg-white rounded-lg shadow px-5 py-4">
                        <h2 class="text-sm font-bold text-navy-800 mb-3">Financial Summary</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total Amount</span>
                                <span class="font-bold text-navy-800">{{ formatMoney($this->totalAmount) }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Advance (PKR)</label>
                                    <input wire:model.live.debounce.300ms="advance" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Discount (PKR)</label>
                                    <input wire:model.live.debounce.300ms="discount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                </div>
                            </div>
                            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                <span class="text-gray-600">Remaining Amount</span>
                                <span class="font-bold text-red-600 text-lg">{{ formatMoney($this->remainingAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Proceed Button --}}
                    <div class="flex justify-end">
                        <button wire:click="proceed" class="px-8 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors">
                            Proceed Sale
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
