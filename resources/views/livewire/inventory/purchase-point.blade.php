<div>
    <div class="max-w-6xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Purchase Point</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">{{ session('success') }}</div>
        @endif

        @if ($purchaseSummary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-green-700">Purchase Saved</h2>
                    <button wire:click="$set('purchaseSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-3 gap-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Supplier</dt><dd class="font-medium">{{ $purchaseSummary['supplier'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Items</dt><dd>{{ $purchaseSummary['items'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Total</dt><dd class="font-bold text-navy-800">{{ formatMoney($purchaseSummary['total']) }}</dd></div>
                </dl>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-6">
            <div class="col-span-2 space-y-4">
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                            <input wire:model="purchase_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                        <x-searchable-select wire-model="supplier_id" :options="$supplierOpts" label="Supplier" placeholder="Search supplier..." />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <x-searchable-select wire-model="selected_product_id" :options="$productOpts" label="Product" placeholder="Search product..." />
                        </div>
                        <div class="w-32">
                            <x-money-input wire-model="line_rate" label="Rate (PKR)" />
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Quantity</label>
                            <input wire:model="line_quantity" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        </div>
                        <button wire:click="addItem" class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">+ Add</button>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-navy-800 text-white">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium">Name</th>
                                <th class="px-4 py-2.5 text-right font-medium">Price</th>
                                <th class="px-4 py-2.5 text-right font-medium">Qty</th>
                                <th class="px-4 py-2.5 text-right font-medium">Total</th>
                                <th class="px-4 py-2.5 w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">
                                    <td class="px-4 py-2 font-medium text-navy-800">{{ $item['name'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">PKR {{ number_format((float) $item['unit_cost'], 0) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">PKR {{ number_format((float) $item['unit_cost'] * $item['quantity'], 0) }}</td>
                                    <td class="px-4 py-2 text-center"><button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Remove</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No items added yet.</td></tr>
                            @endforelse
                        </tbody>
                        @if (count($items) > 0)
                            <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td colspan="3" class="px-4 py-2.5 text-right">Total Amount:</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-navy-800">{{ formatMoney($this->totalAmount) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if (count($items) > 0)
                    <div class="bg-white rounded-lg shadow px-5 py-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none mb-3"></textarea>
                        <div class="flex justify-end">
                            <button wire:click="savePurchase" wire:loading.attr="disabled" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                                <svg wire:loading wire:target="savePurchase" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save Purchase
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Stock Information</h2>
                    @if ($stock_product_name)
                        <dl class="space-y-2 text-sm">
                            <div><dt class="text-gray-500">Product</dt><dd class="font-medium text-navy-800">{{ $stock_product_name }}</dd></div>
                            <div><dt class="text-gray-500">Current Stock</dt><dd class="font-medium">{{ number_format($stock_current_qty) }} units</dd></div>
                            <div><dt class="text-gray-500">Sale Price</dt><dd class="font-medium">{{ $stock_current_price }}</dd></div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-400">Select a product to see stock info.</p>
                    @endif
                </div>

                {{-- Supplier Price Suggestions --}}
                @if (count($supplierPrices) > 0)
                    <div class="bg-white rounded-lg shadow px-5 py-4">
                        <h2 class="text-sm font-bold text-navy-800 mb-3">Supplier Prices</h2>
                        <div class="space-y-1.5">
                            @foreach ($supplierPrices as $sp)
                                <div class="flex items-center justify-between px-3 py-2 rounded text-sm {{ $sp['is_lowest'] ? 'bg-green-50 border border-green-200' : 'bg-gray-50' }}">
                                    <div>
                                        <span class="font-medium {{ $sp['is_lowest'] ? 'text-green-700' : 'text-gray-700' }}">{{ $sp['name'] }}</span>
                                        @if ($sp['is_lowest'])
                                            <span class="ml-1 text-xs text-green-600 font-medium">Best</span>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold tabular-nums {{ $sp['is_lowest'] ? 'text-green-700' : 'text-gray-700' }}">{{ formatMoney($sp['price']) }}</span>
                                        @if ($sp['last_date'])
                                            <span class="block text-xs text-gray-400">{{ $sp['last_date'] }}{{ $sp['last_qty'] ? ' · '.$sp['last_qty'].' pcs' : '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Selected supplier last info --}}
                @if ($selectedSupplierLastInfo)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2">
                        <p class="text-xs text-blue-800">{{ $selectedSupplierLastInfo }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
