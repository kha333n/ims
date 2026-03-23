<div>
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-navy-800">Product List</h1>
            <div class="flex items-center gap-3">
                <div class="relative w-72">
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Search by name..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm
                                  focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button wire:click="openAddModal"
                        class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                    + Add Product
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-navy-800 text-white">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium w-12">Sr#</th>
                        <th class="px-4 py-2.5 text-left font-medium">Name</th>
                        <th class="px-4 py-2.5 text-right font-medium">Price</th>
                        <th class="px-4 py-2.5 text-right font-medium">Quantity</th>
                        <th class="px-4 py-2.5 text-left font-medium">Supplier</th>
                        <th class="px-4 py-2.5 text-center font-medium w-20">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-blue-50 transition-colors" wire:key="product-{{ $product->id }}">
                            <td class="px-4 py-2 text-gray-500">{{ $products->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-2 font-medium text-navy-800">{{ $product->name }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">@money($product->price)</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($product->quantity) }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $product->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                <button wire:click="openEditModal({{ $product->id }})"
                                        class="text-navy-500 hover:text-navy-700 text-xs font-medium">
                                    Edit
                                </button>
                                <button wire:click="confirmDelete({{ $product->id }})"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                @if ($search)
                                    No products found matching "{{ $search }}"
                                @else
                                    No products have been added yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-navy-800">
                        {{ $editingProductId ? 'Edit Product' : 'Add Product' }}
                    </h2>
                </div>

                <form wire:submit="save" class="px-6 py-4 space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Price & Quantity --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (PKR) <span class="text-red-500">*</span></label>
                            <input wire:model="price" type="number" step="0.01" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                            <input wire:model="quantity" type="number" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Supplier --}}
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

                    {{-- Brand & Model --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <input wire:model="brand" type="text"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Model Number</label>
                            <input wire:model="model_number" type="text"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                    </div>

                    {{-- Color & Category --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input wire:model="color" type="text"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input wire:model="category" type="text"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                    </div>

                    {{-- Image --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                        <input wire:model="image" type="file" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-navy-100 file:text-navy-700 hover:file:bg-navy-200">
                        @error('image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" class="mt-2 h-20 rounded border" alt="Preview">
                        @elseif ($existing_image)
                            <p class="mt-1 text-xs text-gray-500">Current image: {{ basename($existing_image) }}</p>
                        @endif
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none resize-none"></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">
                            {{ $editingProductId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Dialog --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="cancelDelete">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Product</h3>
                <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this product? This action can be undone.</p>
                @if ($deleteError)
                    <p class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2 mb-4">{{ $deleteError }}</p>
                @endif
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    @if (!$deleteError)
                        <button wire:click="deleteProduct"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
