<div>
    <div class="max-w-5xl mx-auto">
        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 {{ $actionSummary['action'] === 'Deleted' ? 'border-red-500' : 'border-green-500' }} px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium {{ $actionSummary['action'] === 'Deleted' ? 'text-red-700' : 'text-green-700' }}">Supplier {{ $actionSummary['action'] }}: {{ $actionSummary['name'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-navy-800">Suppliers</h1>
            <div class="flex items-center gap-3">
                <div class="relative w-72">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by ID or name..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button wire:click="openAddModal" class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                    + Add Supplier
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-navy-800 text-white">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium w-12">ID</th>
                        <th class="px-4 py-2.5 text-left font-medium">Name</th>
                        <th class="px-4 py-2.5 text-left font-medium">Contact Person</th>
                        <th class="px-4 py-2.5 text-left font-medium">Phone</th>
                        <th class="px-4 py-2.5 text-left font-medium">Address</th>
                        <th class="px-4 py-2.5 text-center font-medium">Products</th>
                        <th class="px-4 py-2.5 text-center font-medium w-36">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-blue-50 transition-colors" wire:key="sup-{{ $supplier->id }}">
                            <td class="px-4 py-2 text-gray-500">{{ $supplier->id }}</td>
                            <td class="px-4 py-2 font-medium text-navy-800">{{ $supplier->name }}</td>
                            <td class="px-4 py-2">{{ $supplier->contact_person ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $supplier->phone ?? '—' }}</td>
                            <td class="px-4 py-2 text-xs text-gray-600 max-w-xs truncate">{{ $supplier->address ?? '—' }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $supplier->supplier_products_count > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $supplier->supplier_products_count }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center space-x-1">
                                <button wire:click="openPricing({{ $supplier->id }})" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Pricing</button>
                                <button wire:click="openEditModal({{ $supplier->id }})" class="text-navy-500 hover:text-navy-700 text-xs font-medium">Edit</button>
                                <button wire:click="confirmDelete({{ $supplier->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                @if ($search)
                                    No suppliers found matching "{{ $search }}"
                                @else
                                    No suppliers have been added yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="mt-4">{{ $suppliers->links() }}</div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-navy-800">{{ $editingId ? 'Edit Supplier' : 'Add Supplier' }}</h2>
                </div>
                <form wire:submit="save" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                            <input wire:model="contact_person" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input wire:model="phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input wire:model="address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ $editingId ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Pricing Modal --}}
    @if ($showPricingModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closePricing">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[80vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-navy-800">Product Pricing — {{ \App\Models\Supplier::find($pricingSupplier)?->name }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Set the price this supplier charges per product. Leave blank if not supplied.</p>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="border-b">
                                <th class="py-2 text-left font-medium text-gray-600">Product</th>
                                <th class="py-2 text-right font-medium text-gray-600 w-40">Price (PKR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($pricingRows as $index => $row)
                                <tr wire:key="pr-{{ $row['product_id'] }}">
                                    <td class="py-2 text-navy-800">
                                        <span class="text-gray-400 text-xs">#{{ $row['product_id'] }}</span>
                                        {{ $row['name'] }}
                                    </td>
                                    <td class="py-2">
                                        <input type="text" inputmode="decimal" placeholder="—"
                                               x-data="{
                                                   raw: @entangle('pricingRows.' . $index . '.price'),
                                                   _d: '',
                                                   f: false,
                                                   init() { this._d = this.fmt(this.raw); this.$watch('raw', v => { if (!this.f) this._d = this.fmt(v) }) },
                                                   fmt(v) { if (!v || v === '') return ''; const n = parseFloat(String(v).replace(/,/g, '')); return isNaN(n) ? String(v) : n.toLocaleString('en-US', {maximumFractionDigits:2}); }
                                               }"
                                               x-model="_d"
                                               x-on:focus="f = true; _d = String(raw).replace(/,/g, '')"
                                               x-on:blur="f = false; let c = String(_d).replace(/,/g, ''); raw = c; _d = fmt(raw)"
                                               x-on:input="raw = String(_d).replace(/,/g, '')"
                                               class="w-full text-right px-2 py-1.5 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-navy-400 focus:border-navy-400 outline-none tabular-nums">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button wire:click="closePricing" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                    <button wire:click="savePricing" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="savePricing" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save Pricing</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirm --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="cancelDelete">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Supplier</h3>
                <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this supplier? Their pricing records will also be removed.</p>
                @if ($deleteError)
                    <p class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2 mb-4">{{ $deleteError }}</p>
                @endif
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                    @if (!$deleteError)
                        <button wire:click="deleteSupplier" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors">Delete</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
