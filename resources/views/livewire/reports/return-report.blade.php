<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex flex-wrap items-end gap-4">
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
                <input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
                <input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
            </div>
            <div class="w-56">
                <x-searchable-select wire-model="sale_man_id" :options="$smOpts" label="Sale Man" placeholder="All Sale Men" />
            </div>
            <div class="w-56">
                <x-searchable-select wire-model="customer_id" :options="$customerOpts" label="Customer" placeholder="All Customers" />
            </div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                <svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generate
            </button>
        </div>
    </div>

    @if ($generated)
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-navy-800 text-white">
                    <th class="px-2 py-2 text-left font-medium w-8">Serial</th>
                    <th class="px-2 py-2 text-left font-medium">Account</th>
                    <th class="px-2 py-2 text-left font-medium">Return Date</th>
                    <th class="px-2 py-2 text-left font-medium">Customer Name</th>
                    <th class="px-2 py-2 text-left font-medium">Phone</th>
                    <th class="px-2 py-2 text-left font-medium">Recovery Man</th>
                    <th class="px-2 py-2 text-left font-medium">Sale Man</th>
                    <th class="px-2 py-2 text-left font-medium">Item</th>
                    <th class="px-2 py-2 text-right font-medium">Qty</th>
                    <th class="px-2 py-2 text-right font-medium">Total Amount</th>
                    <th class="px-2 py-2 text-right font-medium">Received</th>
                    <th class="px-2 py-2 text-right font-medium">Remaining</th>
                    <th class="px-2 py-2 text-left font-medium">Reason</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($returns as $i => $row)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="px-2 py-1.5 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-2 py-1.5 font-medium">{{ $row['account_id'] }}</td>
                        <td class="px-2 py-1.5">{{ formatDate($row['return_date']) }}</td>
                        <td class="px-2 py-1.5">{{ $row['customer'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['phone'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['recovery_man'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['sale_man'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['item'] }}</td>
                        <td class="px-2 py-1.5 text-right">{{ $row['quantity'] }}</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($row['total_amount'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums text-green-700">@money($row['received'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums {{ $row['remaining'] > 0 ? 'text-red-600' : 'text-green-600' }}">@money($row['remaining'])</td>
                        <td class="px-2 py-1.5">{{ $row['reason'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="13" class="px-4 py-8 text-center text-gray-400">No returns found for the selected period.</td></tr>
                @endforelse
            </tbody>
            @if ($returns->count() > 0)
                <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
                    <tr>
                        <td colspan="9" class="px-2 py-2 text-right">Totals ({{ $returns->count() }} returns):</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($totals['total_amount'])</td>
                        <td class="px-2 py-2 text-right tabular-nums text-green-700">@money($totals['received'])</td>
                        <td class="px-2 py-2 text-right tabular-nums text-red-600">@money($totals['remaining'])</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</div>
