<div>
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-navy-800">Customers</h1>
            <div class="flex items-center gap-3">
                <div class="relative w-72">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                    <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <a href="{{ route('customers.create') }}"
                   class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                    + Add Customer
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-navy-800 text-white">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium w-16">CID</th>
                        <th class="px-4 py-2.5 text-left font-medium">Name</th>
                        <th class="px-4 py-2.5 text-left font-medium">Contact</th>
                        <th class="px-4 py-2.5 text-left font-medium">Address</th>
                        <th class="px-4 py-2.5 text-left font-medium">CNIC</th>
                        <th class="px-4 py-2.5 text-left font-medium">Reference</th>
                        <th class="px-4 py-2.5 text-right font-medium">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-blue-50 transition-colors cursor-pointer"
                            wire:key="cust-{{ $customer->id }}"
                            onclick="window.location='{{ route('customers.show', $customer->id) }}'">
                            <td class="px-4 py-2 text-gray-500">{{ $customer->id }}</td>
                            <td class="px-4 py-2 font-medium text-navy-800">{{ $customer->name }}</td>
                            <td class="px-4 py-2">{{ $customer->mobile ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600 truncate max-w-48">{{ $customer->home_address ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $customer->cnic ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $customer->reference ?? '—' }}</td>
                            <td class="px-4 py-2 text-right tabular-nums font-medium {{ ($customer->total_remaining ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                @money($customer->total_remaining ?? 0)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                @if ($search)
                                    No customers found matching "{{ $search }}"
                                @else
                                    No customers have been added yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="mt-4">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
