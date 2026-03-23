<div>
    <div class="max-w-6xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Purchase Point</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-3 gap-6">
            {{-- Left: Purchase Form --}}
            <div class="col-span-2 space-y-4">
                {{-- Date & Supplier --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                            <input wire:model="purchase_date" type="date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('purchase_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                            <select wire:model="supplier_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                                <option value="">— None —</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Add Item Row --}}
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                            <select wire:model.live="selected_product_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                                <option value="">— Select Product —</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('selected_product_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-32">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rate (PKR)</label>
                            <input wire:model="line_rate" type="number" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('line_rate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input wire:model="line_quantity" type="number" min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('line_quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="addItem"
                                class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                            + Add
                        </button>
                    </div>
                </div>

                {{-- Line Items Table --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-navy-800 text-white">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium">Name</th>
                                <th class="px-4 py-2.5 text-right font-medium">Price</th>
                                <th class="px-4 py-2.5 text-right font-medium">Qty</th>
                                <th class="px-4 py-2.5 text-right font-medium">Total</th>
                                <th class="px-4 py-2.5 text-center font-medium w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">
                                    <td class="px-4 py-2 font-medium text-navy-800">{{ $item['name'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">PKR {{ number_format((float) $item['unit_cost'], 0) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $item['quantity'] }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">PKR {{ number_format((float) $item['unit_cost'] * $item['quantity'], 0) }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                        No items added yet. Select a product above and click "+ Add".
                                    </td>
                                </tr>
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

                {{-- Notes & Save --}}
                @if (count($items) > 0)
                    <div class="bg-white rounded-lg shadow px-5 py-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none resize-none mb-3"></textarea>
                        <div class="flex justify-end">
                            <button wire:click="savePurchase"
                                    class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition-colors">
                                Save Purchase
                            </button>
                        </div>
                        @error('items') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            {{-- Right: Stock Information --}}
            <div>
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Stock Information</h2>
                    @if ($stock_product_name)
                        <dl class="space-y-2 text-sm">
                            <div>
                                <dt class="text-gray-500">Product</dt>
                                <dd class="font-medium text-navy-800">{{ $stock_product_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Current Stock</dt>
                                <dd class="font-medium">{{ number_format($stock_current_qty) }} units</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Current Price</dt>
                                <dd class="font-medium">{{ $stock_current_price }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-400">Select a product to see stock info.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
