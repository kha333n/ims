<div>
    {{-- Filters --}}
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
                <input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
                <input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
            </div>
            <div class="w-64">
                <x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="All Recovery Men" />
            </div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                Generate
            </button>
        </div>
    </div>

    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-navy-800 text-white">
                    <th class="px-2 py-2 text-left font-medium">Acc#</th>
                    <th class="px-2 py-2 text-left font-medium">Date</th>
                    <th class="px-2 py-2 text-left font-medium">SM</th>
                    <th class="px-2 py-2 text-left font-medium">RM</th>
                    <th class="px-2 py-2 text-left font-medium">CID</th>
                    <th class="px-2 py-2 text-left font-medium">Customer</th>
                    <th class="px-2 py-2 text-left font-medium">Phone</th>
                    <th class="px-2 py-2 text-left font-medium">Item</th>
                    <th class="px-2 py-2 text-right font-medium">Total</th>
                    <th class="px-2 py-2 text-right font-medium">Advance</th>
                    <th class="px-2 py-2 text-right font-medium">Paid</th>
                    <th class="px-2 py-2 text-right font-medium">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($accounts as $row)
                    <tr>
                        <td class="px-2 py-1.5 font-medium">{{ $row['id'] }}</td>
                        <td class="px-2 py-1.5">{{ formatDate($row['date']) }}</td>
                        <td class="px-2 py-1.5">{{ $row['sale_man'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['recovery_man'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['customer_id'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['customer'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['phone'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['items'] }}</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($row['total'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($row['advance'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($row['paid'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums font-medium {{ $row['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">@money($row['balance'])</td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="px-4 py-8 text-center text-gray-400">No sales found for the selected period.</td></tr>
                @endforelse
            </tbody>
            @if ($accounts->count() > 0)
                <tfoot class="bg-gray-100 font-bold text-xs">
                    <tr>
                        <td colspan="8" class="px-2 py-2 text-right">Totals:</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($totals['total'])</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($totals['advance'])</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($totals['paid'])</td>
                        <td class="px-2 py-2 text-right tabular-nums text-red-600">@money($totals['balance'])</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</div>
