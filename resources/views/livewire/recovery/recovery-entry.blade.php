<div>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Recovery Entry</h1>

        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-4">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-lg font-bold text-green-700">{{ $actionSummary['action'] }}</h2>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-4 gap-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Recovery Man</dt><dd class="font-medium">{{ $actionSummary['rm'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Category</dt><dd>{{ $actionSummary['category'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Accounts Marked</dt><dd class="font-bold">{{ $actionSummary['count'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Total Collected</dt><dd class="font-bold text-green-700">{{ formatMoney($actionSummary['total']) }}</dd></div>
                </dl>
                @if ($actionSummary['duplicates'] > 0)
                    <p class="mt-2 text-xs text-amber-600">{{ $actionSummary['duplicates'] }} duplicate(s) detected (already paid today) — recorded with note.</p>
                @endif
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow px-5 py-4 mb-4">
            <div class="flex items-end gap-4">
                <div class="w-72">
                    <x-searchable-select wire-model="recovery_man_id" :options="$rmOpts" label="Recovery Man" placeholder="Search RM..." />
                </div>
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                    <select wire:model="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                        <option value="">— Select —</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <button wire:click="load" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                    Load
                </button>
            </div>
        </div>

        {{-- Accounts Table --}}
        @if ($loaded)
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-100 border border-green-200"></span> Paid today</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-100 border border-red-200"></span> Overdue</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-white border border-gray-200"></span> Due</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-100 border border-blue-200"></span> Selected</span>
                </div>
                <div class="text-xs text-gray-400 space-x-2">
                    <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">↑↓</kbd> Navigate
                    <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Space</kbd>/<kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Enter</kbd> Select
                    <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Tab</kbd> Edit amount
                    <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Esc</kbd> Back
                </div>
            </div>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-navy-800 text-white">
                        <tr>
                            <th class="px-3 py-2.5 text-center font-medium w-10">
                                <input type="checkbox" x-data x-on:change="
                                    document.querySelectorAll('[data-recovery-check]').forEach(cb => {
                                        cb.checked = $event.target.checked;
                                        cb.dispatchEvent(new Event('change'));
                                    })
                                " class="rounded">
                            </th>
                            <th class="px-3 py-2.5 text-left font-medium w-16">CID</th>
                            <th class="px-3 py-2.5 text-left font-medium">Customer Name</th>
                            <th class="px-3 py-2.5 text-left font-medium">Phone</th>
                            <th class="px-3 py-2.5 text-left font-medium">Address</th>
                            <th class="px-3 py-2.5 text-right font-medium">Balance</th>
                            <th class="px-3 py-2.5 text-right font-medium w-32">Amount</th>
                            <th class="px-3 py-2.5 text-center font-medium w-16">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 outline-none" tabindex="0"
                           x-ref="recoveryTable"
                           x-init="$nextTick(() => { $el.focus(); focusIdx = 0; });
                                   Livewire.hook('morph.updated', ({el}) => {
                                       if (el === $el || $el.contains(el)) { $nextTick(() => { $el.focus(); focusIdx = 0; }); }
                                   })"
                           x-data="{
                        focusIdx: 0,
                        rows() { return [...$el.querySelectorAll('tr[data-row]')]; },
                        toggle(id) {
                            const cb = document.querySelector(`[data-check-id='${id}']`);
                            if (cb) { cb.checked = !cb.checked; cb.dispatchEvent(new Event('change')); }
                        },
                        focusRow(i) {
                            const r = this.rows();
                            if (r.length === 0) return;
                            if (i < 0) i = 0;
                            if (i >= r.length) i = r.length - 1;
                            this.focusIdx = i;
                        },
                        ensureFocus() {
                            if (this.focusIdx < 0) this.focusIdx = 0;
                        },
                        editAmount() {
                            const r = this.rows()[this.focusIdx];
                            if (r) { const inp = r.querySelector('input[type=number]'); if (inp) inp.focus(); }
                        }
                    }"
                    x-on:keydown.down.prevent="ensureFocus(); focusRow(focusIdx + 1)"
                    x-on:keydown.up.prevent="ensureFocus(); focusRow(focusIdx - 1)"
                    x-on:keydown.space.prevent="ensureFocus(); { const r = rows()[focusIdx]; if (r) toggle(r.dataset.row); }"
                    x-on:keydown.enter.prevent="ensureFocus(); { const r = rows()[focusIdx]; if (r) toggle(r.dataset.row); }"
                    x-on:keydown.tab.prevent="ensureFocus(); editAmount()"
                    >
                        @forelse ($accounts as $index => $account)
                            @php
                                $isDuplicate = $paidToday[$account->id] ?? false;
                                $isOverdue = !$isDuplicate && $account->remaining_amount > $account->installment_amount;
                                $statusBg = $isDuplicate ? '#dcfce7' : ($isOverdue ? '#fee2e2' : '#ffffff');
                                $selectedBg = '#bfdbfe';
                            @endphp
                            <tr class="cursor-pointer outline-none border-l-4"
                                wire:key="rec-{{ $account->id }}"
                                data-row="{{ $account->id }}"
                                x-on:click="if ($event.target.tagName !== 'INPUT') { focusIdx = {{ $index }}; toggle({{ $account->id }}); }"
                                :style="{
                                    backgroundColor: focusIdx === {{ $index }}
                                        ? '#fde68a'
                                        : (document.querySelector(`[data-check-id='{{ $account->id }}']`)?.checked ? '{{ $selectedBg }}' : '{{ $statusBg }}'),
                                    borderLeftColor: focusIdx === {{ $index }} ? '#1e4d8c' : 'transparent',
                                }">
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" wire:model="checked.{{ $account->id }}" data-recovery-check data-check-id="{{ $account->id }}" class="rounded text-navy-600 focus:ring-navy-400 pointer-events-none" tabindex="-1">
                                </td>
                                <td class="px-3 py-2 text-gray-500">{{ $account->customer_id }}</td>
                                <td class="px-3 py-2 font-medium text-navy-800">{{ $account->customer->name }}</td>
                                <td class="px-3 py-2">{{ $account->customer->mobile ?? '—' }}</td>
                                <td class="px-3 py-2 text-gray-600 truncate max-w-40">{{ $account->customer->home_address ?? '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums font-medium text-red-600">@money($account->remaining_amount)</td>
                                <td class="px-3 py-1 text-right" x-on:click.stop>
                                    <input type="text" inputmode="decimal"
                                           x-data="{
                                               raw: @entangle('amounts.' . $account->id),
                                               get display() { return this._display },
                                               set display(v) { this._display = v },
                                               _display: '',
                                               focused: false,
                                               init() {
                                                   this._display = this.fmt(this.raw);
                                                   this.$watch('raw', v => { if (!this.focused) this._display = this.fmt(v) });
                                               },
                                               fmt(v) {
                                                   if (!v || v === '') return '';
                                                   const n = parseFloat(String(v).replace(/,/g, ''));
                                                   return isNaN(n) ? String(v) : n.toLocaleString('en-US', {maximumFractionDigits:2});
                                               }
                                           }"
                                           x-model="display"
                                           x-on:focus="focused = true; display = String(raw).replace(/,/g, '')"
                                           x-on:blur="focused = false; let c = String(display).replace(/,/g, ''); raw = c === '' ? '0' : c; display = fmt(raw)"
                                           x-on:input="raw = String(display).replace(/,/g, '')"
                                           class="w-full px-2 py-1 border border-gray-300 rounded text-xs text-right tabular-nums focus:ring-1 focus:ring-navy-400 outline-none"
                                           tabindex="-1"
                                           x-on:keydown.escape="$el.blur(); $el.closest('tbody').focus()">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if ($isDuplicate)
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-green-100 text-green-700 font-medium" title="Already paid today">Paid</span>
                                    @elseif ($isOverdue)
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-700 font-medium" title="Balance exceeds one installment">Overdue</span>
                                    @else
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-gray-100 text-gray-500">Due</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">No active accounts found for this RM and category.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @error('checked') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror

            @if ($accounts->count() > 0)
                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xs text-gray-400 space-x-3">
                        <span><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">↑↓</kbd> Navigate</span>
                        <span><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Space</kbd> / <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Enter</kbd> Select</span>
                        <span><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Tab</kbd> Edit amount</span>
                        <span><kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600 font-mono">Esc</kbd> Back to rows</span>
                    </div>
                    <button wire:click="updateStatus" wire:loading.attr="disabled" class="px-6 py-2.5 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                        <svg wire:loading wire:target="updateStatus" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Update Status
                    </button>
                </div>
            @endif
        @endif
    </div>

    {{-- Duplicate Warning Popup --}}
    @if ($showDuplicateWarning)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Duplicate Payment Warning</h3>
                </div>
                <p class="text-sm text-gray-600 mb-2">
                    You selected <strong class="text-amber-700">{{ $duplicateCount }}</strong> account(s) that have <strong>already been paid today</strong>.
                </p>
                <p class="text-sm text-gray-600 mb-4">
                    Continuing will create duplicate payment entries and deduct from their balance again. Only proceed if this is intentional.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDuplicateWarning" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="confirmDuplicateUpdate" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-500 rounded-lg transition-colors">
                        Continue Anyway
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
