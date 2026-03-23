<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Company Settings</h1>

        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700">{{ $actionSummary['action'] }}: {{ $actionSummary['detail'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                <input wire:model="company_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                <textarea wire:model="company_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company Phone</label>
                <input wire:model="company_phone" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Defaulter Days Threshold</label>
                <input wire:model="defaulter_days" type="number" min="1" class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                <p class="mt-1 text-xs text-gray-400">Accounts older than this many days with balance are flagged as defaulters</p>
            </div>
            <div class="flex justify-end">
                <button wire:click="save" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">Save Settings</button>
            </div>
        </div>
    </div>
</div>
