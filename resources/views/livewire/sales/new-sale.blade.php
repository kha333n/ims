<div>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">New Sale</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary after save --}}
        @if ($summary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-green-700">Sale Completed</h2>
                    <button wire:click="dismissSummary" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-3 gap-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Account #</dt><dd class="font-bold text-navy-800">#{{ $summary['account_id'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $summary['customer'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Items</dt><dd>{{ $summary['items'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Total</dt><dd class="font-medium">{{ formatMoney($summary['total']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Advance</dt><dd>{{ formatMoney($summary['advance']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Discount</dt><dd>{{ formatMoney($summary['discount']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Remaining</dt><dd class="font-bold text-red-600">{{ formatMoney($summary['remaining']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Installment</dt><dd>{{ $summary['installment_type'] }} — {{ formatMoney($summary['installment_amount']) }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Staff</dt><dd>SM: {{ $summary['sale_man'] }} / RM: {{ $summary['recovery_man'] }}</dd></div>
                </dl>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('customers.show', $summary['account_id']) }}" class="text-xs text-navy-600 hover:underline">View Customer</a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            {{-- Left: Customer Panel --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Customer</h2>
                    <div class="space-y-3">
                        <div class="flex items-end gap-2">
                            <div class="flex-1" wire:key="customer-select-{{ $customerSelectKey }}">
                                <x-searchable-select wire-model="customer_id" :options="$customerOpts" label="Party ID" placeholder="Search by ID or name..." :required="true" />
                            </div>
                            <button type="button" wire:click="openNewCustomerModal" class="px-3 py-2 text-xs font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors whitespace-nowrap mb-0.5" title="Add New Customer">
                                + New
                            </button>
                        </div>
                        @error('customer_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror

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
                        <div class="grid {{ $installment_type === 'daily' ? 'grid-cols-1' : 'grid-cols-2' }} gap-3">
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
                                    <select wire:model="installment_day" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                        <option value="">— Select —</option>
                                        @for ($d = 1; $d <= 31; $d++)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endfor
                                    </select>
                                    @error('installment_day') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @endif
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Per {{ ucfirst($installment_type === 'daily' ? 'day' : ($installment_type === 'weekly' ? 'week' : 'month')) }} Amount (PKR)</label>
                                <x-money-input wire-model="installment_amount" :live="true" />
                                @error('installment_amount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @if ($this->totalInstallments !== null)
                            <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2">
                                <p class="text-xs text-blue-800">Estimated: <strong>{{ $this->totalInstallments }} {{ $this->periodLabel }}</strong> to complete</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Staff Assignment --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Staff Assignment</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-searchable-select wire-model="sale_man_id" :options="$smOpts" label="Sale Man" placeholder="Search sale man..." :required="true" />
                            @error('sale_man_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="Search recovery man..." :required="true" />
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
                            <x-searchable-select wire-model="selected_product_id" :options="$productOpts" label="Product" placeholder="Search product..." />
                            @error('selected_product_id') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-28">
                            <x-money-input wire-model="item_price" label="Price" />
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
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No items added yet.</td></tr>
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
                                    <x-money-input wire-model="advance" label="Advance (PKR)" :live="true" />
                                </div>
                                <div>
                                    <x-money-input wire-model="discount" label="Discount (PKR)" :live="true" />
                                </div>
                            </div>
                            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                <span class="text-gray-600">Remaining Amount</span>
                                <span class="font-bold text-red-600 text-lg">{{ formatMoney($this->remainingAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="proceed" wire:loading.attr="disabled" class="px-8 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors disabled:opacity-50">
                            <svg wire:loading wire:target="proceed" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save Sale
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- New Customer Modal --}}
    @if ($showNewCustomerModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeNewCustomerModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-navy-800">Add New Customer</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Customer will be auto-selected after saving.</p>
                </div>
                <form wire:submit="saveNewCustomer" class="px-6 py-4 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input wire:model="new_customer_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none" autofocus>
                        @error('new_customer_name') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Father Name</label>
                            <input wire:model="new_customer_father" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mobile</label>
                            <input wire:model="new_customer_mobile" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">CNIC</label>
                            <input wire:model="new_customer_cnic" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Reference</label>
                            <input wire:model="new_customer_reference" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Home Address</label>
                        <input wire:model="new_customer_home_address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Shop Address</label>
                        <input wire:model="new_customer_shop_address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeNewCustomerModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="saveNewCustomer" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save & Select</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
