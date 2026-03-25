<div>
    {{-- Filter panel --}}
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-64">
                <x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="All Recovery Men" />
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500 mb-1">Days Overdue</label>
                <input wire:model="days_overdue" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
            </div>
            <button wire:click="generate" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                <svg wire:loading wire:target="generate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generate
            </button>
        </div>
    </div>

    @if ($generated)
        {{-- Main table --}}
        <table class="w-full text-xs border-collapse mb-6">
            <thead>
                <tr class="bg-navy-800 text-white">
                    <th class="px-2 py-2 text-left w-7">Serial</th>
                    <th class="px-2 py-2 text-left">Account</th>
                    <th class="px-2 py-2 text-left">Sale Date</th>
                    <th class="px-2 py-2 text-right">Days Since Sale</th>
                    <th class="px-2 py-2 text-left">Last Payment Date</th>
                    <th class="px-2 py-2 text-right">Days Since Payment</th>
                    <th class="px-2 py-2 text-left">Customer Name</th>
                    <th class="px-2 py-2 text-left">Phone</th>
                    <th class="px-2 py-2 text-left">Address</th>
                    <th class="px-2 py-2 text-left">Sale Man</th>
                    <th class="px-2 py-2 text-left">Item</th>
                    <th class="px-2 py-2 text-right">Total Amount</th>
                    <th class="px-2 py-2 text-right">Paid</th>
                    <th class="px-2 py-2 text-right">Remaining</th>
                    <th class="px-2 py-2 text-right">Installment Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($defaulters as $i => $d)
                    <tr class="{{ $d['band']['rowClass'] }}">
                        <td class="px-2 py-1.5">{{ $i + 1 }}</td>
                        <td class="px-2 py-1.5 font-bold">#{{ $d['id'] }}</td>
                        <td class="px-2 py-1.5">{{ formatDate($d['sale_date']) }}</td>
                        <td class="px-2 py-1.5 text-right font-bold">{{ $d['days_since_sale'] }}</td>
                        <td class="px-2 py-1.5">{{ $d['last_payment_date'] ? formatDate($d['last_payment_date']) : '— Never —' }}</td>
                        <td class="px-2 py-1.5 text-right font-bold {{ $d['days_since_payment'] > 90 ? 'text-red-600' : ($d['days_since_payment'] > 60 ? 'text-orange-600' : 'text-gray-700') }}">{{ $d['days_since_payment'] }}</td>
                        <td class="px-2 py-1.5">{{ $d['customer'] }}</td>
                        <td class="px-2 py-1.5">{{ $d['phone'] }}</td>
                        <td class="px-2 py-1.5">{{ $d['address'] }}</td>
                        <td class="px-2 py-1.5">{{ $d['sale_man'] }}</td>
                        <td class="px-2 py-1.5">{{ $d['items'] ?: '—' }}</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($d['total'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($d['paid'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums font-bold text-red-600">@money($d['remaining'])</td>
                        <td class="px-2 py-1.5 text-right tabular-nums">@money($d['installment_amount'])</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="px-4 py-8 text-center text-gray-400">No defaulters found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($defaulters->count() > 0)
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="11" class="px-2 py-2 text-right">Totals ({{ $defaulters->count() }} defaulters):</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($defaulters->sum('total'))</td>
                        <td class="px-2 py-2 text-right tabular-nums">@money($defaulters->sum('paid'))</td>
                        <td class="px-2 py-2 text-right tabular-nums text-red-600">@money($defaulters->sum('remaining'))</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>

        {{-- Severity band summary --}}
        @if ($defaulters->count() > 0 && count($bandSummary) > 0)
            <div class="mt-2">
                <h3 class="text-sm font-bold text-navy-800 mb-2">Severity Band Summary</h3>
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-3 py-2 text-left">Severity Band</th>
                            <th class="px-3 py-2 text-right">Number of Defaulters</th>
                            <th class="px-3 py-2 text-right">Total Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($bandSummary as $band)
                            @if ($band['count'] > 0)
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded text-xs {{ $band['badgeClass'] }}">{{ $band['label'] }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-bold">{{ $band['count'] }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums font-bold text-red-600">@money($band['outstanding'])</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td class="px-3 py-2">Grand Total</td>
                            <td class="px-3 py-2 text-right">{{ $defaulters->count() }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-red-600">@money($defaulters->sum('remaining'))</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endif
</div>
