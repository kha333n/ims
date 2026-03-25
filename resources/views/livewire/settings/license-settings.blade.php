<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">License Settings</h1>

        @if ($actionSummary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 {{ $actionSummary['type'] === 'success' ? 'border-green-500' : 'border-red-500' }} px-6 py-5">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-lg font-bold {{ $actionSummary['type'] === 'success' ? 'text-green-700' : 'text-red-700' }}">{{ $actionSummary['title'] }}</h2>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <p class="text-sm text-gray-600">{{ $actionSummary['message'] }}</p>
            </div>
        @endif

        {{-- Current Status --}}
        <div class="bg-white rounded-lg shadow px-6 py-5 mb-6">
            <h2 class="text-sm font-bold text-navy-800 mb-4">License Status</h2>

            <div class="flex items-center gap-3 mb-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium
                    {{ $status['color'] === 'green' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $status['color'] === 'red' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $status['color'] === 'orange' ? 'bg-orange-100 text-orange-700' : '' }}">
                    <span class="w-2 h-2 rounded-full
                        {{ $status['color'] === 'green' ? 'bg-green-500' : '' }}
                        {{ $status['color'] === 'red' ? 'bg-red-500' : '' }}
                        {{ $status['color'] === 'orange' ? 'bg-orange-500' : '' }}"></span>
                    {{ $status['label'] }}
                </span>
            </div>

            @if ($status['key'])
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs mb-0.5">License Key</dt>
                        <dd class="font-mono font-medium">{{ $status['key'] }}</dd>
                    </div>
                    @if ($status['customer_name'])
                        <div>
                            <dt class="text-gray-500 text-xs mb-0.5">Customer</dt>
                            <dd class="font-medium">{{ $status['customer_name'] }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 text-xs mb-0.5">Expires</dt>
                        <dd class="font-medium {{ now()->isAfter($status['expires_at'] ?? '2099-01-01') ? 'text-red-600' : '' }}">
                            {{ $status['expires_at'] ? formatDate($status['expires_at']) : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs mb-0.5">Last Online Check</dt>
                        <dd class="font-medium">
                            {{ $status['last_verified'] ? \Carbon\Carbon::parse($status['last_verified'])->diffForHumans() : 'Never' }}
                        </dd>
                    </div>
                </dl>

                <div class="flex gap-2 mt-4 pt-4 border-t">
                    <button wire:click="refreshOnline" class="px-4 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                        Verify Online
                    </button>
                    <button wire:click="$set('showDeactivateConfirm', true)" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-lg transition-colors">
                        Deactivate
                    </button>
                </div>
            @endif
        </div>

        {{-- Deactivate Confirmation --}}
        @if ($showDeactivateConfirm)
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg px-6 py-4">
                <p class="text-sm text-red-800 mb-3">Are you sure? This will free the hardware slot so the license can be used on another machine. <strong>All app features will be blocked until reactivated.</strong></p>
                <div class="flex gap-2">
                    <button wire:click="deactivate" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-medium rounded-lg">Yes, Deactivate</button>
                    <button wire:click="$set('showDeactivateConfirm', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg">Cancel</button>
                </div>
            </div>
        @endif

        {{-- Activate New Key --}}
        @if ($status['status'] !== 'valid')
            <div class="bg-white rounded-lg shadow px-6 py-5 mb-6">
                <h2 class="text-sm font-bold text-navy-800 mb-3">Activate License</h2>
                <div class="flex gap-3">
                    <input wire:model="licenseKey" type="text" placeholder="IMS-XXXX-XXXX-XXXX" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-navy-400 outline-none">
                    <button wire:click="activate" wire:loading.attr="disabled" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                        <svg wire:loading wire:target="activate" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Activate
                    </button>
                </div>
                @error('licenseKey') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Hardware ID (for support) --}}
        <div class="bg-gray-50 rounded-lg px-6 py-4">
            <h2 class="text-xs font-medium text-gray-500 mb-1">Hardware ID (for support)</h2>
            <p class="font-mono text-xs text-gray-600 select-all break-all">{{ $status['hardware_id'] }}</p>
        </div>
    </div>
</div>
