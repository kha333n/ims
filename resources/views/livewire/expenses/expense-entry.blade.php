<div>
    <div class="max-w-6xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Daily Expenses</h1>

        @if ($actionSummary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-green-700">Expense Recorded</h2>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>
                <dl class="grid grid-cols-4 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs">Expense #</dt>
                        <dd class="font-medium text-navy-800">#{{ $actionSummary['id'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Description</dt>
                        <dd class="font-medium">{{ $actionSummary['description'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Category</dt>
                        <dd>{{ $actionSummary['category'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Amount</dt>
                        <dd class="font-bold text-navy-800">{{ formatMoney($actionSummary['amount']) }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        <div class="grid grid-cols-5 gap-6">
            {{-- Left: Entry Form --}}
            <div class="col-span-2">
                <div class="bg-white rounded-lg shadow px-5 py-5">
                    <h2 class="text-sm font-bold text-navy-800 mb-4">Record Expense</h2>

                    <div class="space-y-4">
                        <div>
                            <x-money-input wire-model="amount" label="Amount (PKR)" :required="true" />
                            @error('amount')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Description <span class="text-red-500">*</span></label>
                            <input wire:model="description"
                                   type="text"
                                   required
                                   maxlength="500"
                                   placeholder="e.g. Office supplies, Fuel..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date <span class="text-red-500">*</span></label>
                            <input wire:model="expense_date"
                                   type="date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            @error('expense_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                            <input wire:model="category"
                                   type="text"
                                   list="category-suggestions"
                                   maxlength="255"
                                   placeholder="e.g. Transport, Utilities..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                            <datalist id="category-suggestions">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            @error('category')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-1">
                            <button wire:click="save"
                                    wire:loading.attr="disabled"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <svg wire:loading wire:target="save" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Save Expense
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Today's Expenses --}}
            <div class="col-span-3">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-navy-800">Today's Expenses</h2>
                        <span class="text-xs text-gray-400">{{ now()->format('d/M/Y') }}</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-navy-800 text-white">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-medium">Description</th>
                                <th class="px-4 py-2.5 text-left font-medium">Category</th>
                                <th class="px-4 py-2.5 text-right font-medium">Amount</th>
                                <th class="px-4 py-2.5 text-right font-medium">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($todayExpenses as $expense)
                                <tr wire:key="expense-{{ $expense->id }}" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-navy-800">{{ $expense->description }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $expense->category ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums font-medium">{{ formatMoney($expense->amount) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-400 text-xs tabular-nums">{{ $expense->created_at->format('h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No expenses recorded today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($todayExpenses->isNotEmpty())
                            <tfoot class="bg-gray-50 font-semibold border-t border-gray-200">
                                <tr>
                                    <td colspan="2" class="px-4 py-2.5 text-right text-gray-600">Total:</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-navy-800">{{ formatMoney($todayExpenses->sum('amount')) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
