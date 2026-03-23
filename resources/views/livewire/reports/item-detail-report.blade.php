<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($generated)
        @forelse ($items as $group)
            <div class="mb-4">
                <h3 class="text-sm font-bold text-navy-800 mb-1">{{ $group['name'] }} — Qty: {{ $group['quantity'] }} — Total: {{ formatMoney($group['total']) }}</h3>
                <table class="w-full text-xs border-collapse mb-2">
                    <thead><tr class="bg-gray-100"><th class="px-2 py-1 text-left">Date</th><th class="px-2 py-1 text-right">Price</th><th class="px-2 py-1 text-right">Qty</th><th class="px-2 py-1 text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($group['rows'] as $row)
                            <tr><td class="px-2 py-1">{{ formatDate($row['date']) }}</td><td class="px-2 py-1 text-right tabular-nums">@money($row['price'])</td><td class="px-2 py-1 text-right">{{ $row['quantity'] }}</td><td class="px-2 py-1 text-right tabular-nums">@money($row['price'] * $row['quantity'])</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <p class="text-center text-gray-400 py-8">No items found for the selected period.</p>
        @endforelse
    @endif
</div>
