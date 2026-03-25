<div>
    <div class="max-w-6xl mx-auto">
        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 {{ $actionSummary['action'] === 'Deleted' ? 'border-red-500' : 'border-green-500' }} px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium {{ $actionSummary['action'] === 'Deleted' ? 'text-red-700' : 'text-green-700' }}">Recovery Man {{ $actionSummary['action'] }}: {{ $actionSummary['name'] }} ({{ $actionSummary['area'] }})</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-navy-800">Recovery Men</h1>
            <div class="flex items-center gap-3">
                <div class="relative w-72">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button wire:click="openAddModal" class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                    + Add Recovery Man
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-navy-800 text-white">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium w-12">ID</th>
                        <th class="px-4 py-2.5 text-left font-medium">Name</th>
                        <th class="px-4 py-2.5 text-left font-medium">Phone</th>
                        <th class="px-4 py-2.5 text-left font-medium">Area</th>
                        <th class="px-4 py-2.5 text-left font-medium">Rank</th>
                        <th class="px-4 py-2.5 text-center font-medium w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($employees as $emp)
                        <tr class="hover:bg-blue-50 transition-colors" wire:key="emp-{{ $emp->id }}">
                            <td class="px-4 py-2 text-gray-500">{{ $emp->id }}</td>
                            <td class="px-4 py-2 font-medium text-navy-800">{{ $emp->name }}</td>
                            <td class="px-4 py-2">{{ $emp->phone ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $emp->area ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $emp->rank ?? '—' }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                <button wire:click="openEditModal({{ $emp->id }})" class="text-navy-500 hover:text-navy-700 text-xs font-medium">Edit</button>
                                <button wire:click="confirmDelete({{ $emp->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                @if ($search)
                                    No recovery men found matching "{{ $search }}"
                                @else
                                    No recovery men have been added yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="mt-4">{{ $employees->links() }}</div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-navy-800">{{ $editingId ? 'Edit Recovery Man' : 'Add Recovery Man' }}</h2>
                </div>
                <form wire:submit="save" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input wire:model="phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                            <input wire:model="cnic" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input wire:model="address" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Area</label>
                            <input wire:model="area" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rank</label>
                            <input wire:model="rank" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <x-money-input wire-model="salary" label="Salary (PKR)" />
                        @error('salary') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ $editingId ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirm --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="cancelDelete">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Delete Recovery Man</h3>
                <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this recovery man?</p>
                @if ($deleteError)
                    <p class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2 mb-4">{{ $deleteError }}</p>
                @endif
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                    @if (!$deleteError)
                        <button wire:click="deleteEmployee" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors">Delete</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
