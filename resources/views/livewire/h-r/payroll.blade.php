<div>
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-navy-800">Payroll — {{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('F Y') }}</h1>
            <button wire:click="accrueSalaries" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50">
                <svg wire:loading wire:target="accrueSalaries" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Accrue Monthly Salaries
            </button>
        </div>

        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700">{{ $actionSummary['action'] }}: {{ $actionSummary['detail'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-navy-800 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium">Employee</th>
                        <th class="px-4 py-2 text-left font-medium">Type</th>
                        <th class="px-4 py-2 text-right font-medium">Monthly Salary</th>
                        <th class="px-4 py-2 text-right font-medium">Commission %</th>
                        <th class="px-4 py-2 text-right font-medium">Pending Commission</th>
                        <th class="px-4 py-2 text-right font-medium">Balance</th>
                        <th class="px-4 py-2 text-right font-medium">Paid This Month</th>
                        <th class="px-4 py-2 text-right font-medium">Total Due</th>
                        <th class="px-4 py-2 text-center font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($employees as $emp)
                        <tr class="{{ $emp['total_due'] < 0 ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-2 font-medium text-navy-800">{{ $emp['name'] }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $emp['type'] }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">
                                @if ($emp['salary'] > 0)
                                    {{ formatMoney($emp['salary']) }}
                                    @if ($emp['salary_accrued'])
                                        <span class="text-xs text-green-600 ml-1" title="Accrued this month">&#10003;</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">{{ $emp['commission_percent'] > 0 ? $emp['commission_percent'] . '%' : '—' }}</td>
                            <td class="px-4 py-2 text-right tabular-nums {{ $emp['pending_commission'] > 0 ? 'text-orange-600 font-medium' : 'text-gray-400' }}">
                                {{ $emp['pending_commission'] > 0 ? formatMoney($emp['pending_commission']) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-right tabular-nums font-medium {{ $emp['balance'] < 0 ? 'text-red-600' : ($emp['balance'] > 0 ? 'text-green-700' : '') }}">
                                {{ formatMoney(abs($emp['balance'])) }}{{ $emp['balance'] < 0 ? ' (owed)' : '' }}
                            </td>
                            <td class="px-4 py-2 text-right tabular-nums text-gray-600">
                                {{ $emp['paid_this_month'] > 0 ? formatMoney($emp['paid_this_month']) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-right tabular-nums font-bold {{ $emp['total_due'] > 0 ? 'text-navy-800' : ($emp['total_due'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                {{ formatMoney(abs($emp['total_due'])) }}{{ $emp['total_due'] < 0 ? ' (over)' : '' }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button wire:click="openPayModal({{ $emp['id'] }})" class="px-3 py-1 text-xs font-medium text-white bg-green-600 hover:bg-green-500 rounded transition-colors">
                                    Pay
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pay Modal --}}
    @if ($showPayModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closePayModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Pay {{ $payingEmployeeName }}</h3>
                <div class="space-y-3">
                    <div>
                        <x-money-input wire-model="payAmount" label="Payment Amount (PKR)" :required="true" />
                        @error('payAmount') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                        <input wire:model="payDescription" type="text" placeholder="e.g. March salary + commission" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-5">
                    <button wire:click="closePayModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                    <button wire:click="processPayment" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors disabled:opacity-50">
                        <svg wire:loading wire:target="processPayment" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Process Payment
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
