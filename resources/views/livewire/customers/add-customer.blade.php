<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">New Customer</h1>

        @if (session()->has('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">{{ session('success') }}</div>
        @endif

        @if ($savedSummary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-green-700">Customer Saved</h2>
                    <button wire:click="$set('savedSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500 text-xs">Customer ID</dt><dd class="font-bold text-navy-800">#{{ $savedSummary['id'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Name</dt><dd class="font-medium">{{ $savedSummary['name'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">Mobile</dt><dd>{{ $savedSummary['mobile'] }}</dd></div>
                    <div><dt class="text-gray-500 text-xs">CNIC</dt><dd>{{ $savedSummary['cnic'] }}</dd></div>
                </dl>
                <div class="mt-3">
                    <a href="{{ route('customers.show', $savedSummary['id']) }}" class="text-xs text-navy-600 hover:underline">View Customer Detail</a>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Father Name</label>
                    <input wire:model="father_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                        <input wire:model="mobile" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CNIC</label>
                        <input wire:model="cnic" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                    <input wire:model="reference" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Home Address</label>
                    <textarea wire:model="home_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shop Address</label>
                    <textarea wire:model="shop_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</a>
                    <button wire:click="save" type="button" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Save Customer</button>
                </div>
            </div>
        </div>
    </div>
</div>
