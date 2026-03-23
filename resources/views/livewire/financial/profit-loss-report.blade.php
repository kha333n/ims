<div>
    <div class="no-print bg-gray-50 rounded-lg px-5 py-4 mb-4">
        <div class="flex items-end gap-4">
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date From</label><input wire:model="date_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <div class="w-40"><label class="block text-xs font-medium text-gray-500 mb-1">Date To</label><input wire:model="date_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none"></div>
            <button wire:click="generate" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg">Generate</button>
        </div>
    </div>
    @if ($data)
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow px-6 py-5">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-1"><span class="text-gray-600">Sales Revenue</span><span class="font-bold text-navy-800">{{ formatMoney($data['revenue']) }}</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Cost of Goods Sold</span><span class="text-red-600">- {{ formatMoney($data['cogs']) }}</span></div>
                <div class="flex justify-between py-2 border-t border-b border-gray-200 font-bold"><span>Gross Profit</span><span class="{{ $data['gross'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ formatMoney(abs($data['gross'])) }}</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Discounts Given</span><span class="text-red-600">- {{ formatMoney($data['discounts']) }}</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Losses / Write-offs</span><span class="text-red-600">- {{ formatMoney($data['losses']) }}</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-600">Returns</span><span class="text-red-600">- {{ formatMoney($data['returns']) }}</span></div>
                <div class="flex justify-between py-3 border-t-2 border-navy-800 text-lg font-bold"><span>Net Profit</span><span class="{{ $data['net'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ formatMoney(abs($data['net'])) }}</span></div>
            </div>
        </div>
    @endif
</div>
