<div>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Problem Entry</h1>

        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700">{{ $actionSummary['action'] }}: {{ $actionSummary['detail'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-3 gap-6">
            {{-- Left: Form --}}
            <div class="col-span-2 space-y-4">
                <div class="bg-white rounded-lg shadow px-5 py-4 space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <x-searchable-select wire-model="customer_id" :options="$custOpts" label="Customer" placeholder="Search by ID or name..." :required="true" />
                        @if ($customer_id)
                            <x-searchable-select wire-model="account_id" :options="$accOpts" label="Account" placeholder="Select account..." :required="true" />
                        @endif
                    </div>

                    @if ($customer_name)
                        <div class="bg-gray-50 rounded-lg px-4 py-2">
                            <dl class="grid grid-cols-3 gap-2 text-sm">
                                <div><dt class="text-gray-500 text-xs">Customer</dt><dd class="font-medium">{{ $customer_name }}</dd></div>
                                <div><dt class="text-gray-500 text-xs">Phone</dt><dd>{{ $customer_phone }}</dd></div>
                                <div><dt class="text-gray-500 text-xs">Items</dt><dd>{{ $items_list ?? '—' }}</dd></div>
                            </dl>
                        </div>
                    @endif

                    @if ($account_id)
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Manager</label><input wire:model="manager" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Checker</label><input wire:model="checker" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Branch</label><input wire:model="branch" type="text" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Problem <span class="text-red-500">*</span></label>
                            <textarea wire:model="problem_text" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
                            @error('problem_text') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">Previous Promise Date</label><input wire:model="previous_promise_date" type="date" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none" readonly></div>
                            <div><label class="block text-xs font-medium text-gray-500 mb-1">New Commitment Date</label><input wire:model="new_commitment_date" type="date" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Action Taken</label>
                            <textarea wire:model="action_taken" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input wire:model="closed" type="checkbox" class="rounded text-navy-600 focus:ring-navy-400">
                                Mark as Closed
                            </label>
                            <button wire:click="save" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save Problem</button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: History --}}
            <div>
                <div class="bg-white rounded-lg shadow px-5 py-4">
                    <h2 class="text-sm font-bold text-navy-800 mb-3">Problem History</h2>
                    @if ($history->count() > 0)
                        <div class="space-y-3">
                            @foreach ($history as $prob)
                                <div class="border-l-2 {{ $prob->closed ? 'border-green-400' : 'border-red-400' }} pl-3 text-xs">
                                    <p class="font-medium text-gray-700">{{ $prob->problem_text }}</p>
                                    @if ($prob->action_taken)<p class="text-gray-500 mt-0.5">Action: {{ $prob->action_taken }}</p>@endif
                                    <div class="flex gap-3 mt-1 text-gray-400">
                                        @if ($prob->new_commitment_date)<span>Commitment: {{ formatDate($prob->new_commitment_date) }}</span>@endif
                                        <span>{{ $prob->created_at->format('d/M/Y') }}</span>
                                        @if ($prob->closed)<span class="text-green-600 font-medium">Closed</span>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400">{{ $account_id ? 'No problems recorded yet.' : 'Select an account to see history.' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
