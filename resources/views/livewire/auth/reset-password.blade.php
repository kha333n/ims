<div class="w-full max-w-md mx-4">
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-white">Reset Admin Password</h1>
    </div>

    <div class="bg-white rounded-xl shadow-2xl px-6 py-6">
        @if ($success)
            <div class="text-center space-y-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-green-700">Password reset successfully!</p>
                <a href="{{ route('login') }}" class="inline-block px-6 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Go to Login</a>
            </div>
        @elseif (!$method)
            {{-- Choose method --}}
            <p class="text-sm text-gray-600 mb-4">Choose a reset method:</p>
            <div class="space-y-3">
                <button wire:click="$set('method', 'recovery_key')" class="w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="font-medium text-navy-800">Recovery Key</span>
                    <p class="text-xs text-gray-500 mt-0.5">Use the 24-character key from setup</p>
                </button>
                <button wire:click="$set('method', 'support_code')" class="w-full px-4 py-3 text-left text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <span class="font-medium text-navy-800">Support Code</span>
                    <p class="text-xs text-gray-500 mt-0.5">Contact support to get a temporary code</p>
                </button>
            </div>
        @elseif ($method === 'recovery_key')
            <h2 class="text-sm font-bold text-navy-800 mb-3">Reset with Recovery Key</h2>
            @if ($error)
                <div class="mb-3 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $error }}</div>
            @endif
            <form wire:submit="resetWithRecoveryKey" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Recovery Key</label>
                    <input wire:model="recovery_key" type="text" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX-XXXX" autofocus
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                    <input wire:model="new_password" type="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('new_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input wire:model="new_password_confirmation" type="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('method', '')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Back</button>
                    <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Reset Password</button>
                </div>
            </form>
        @elseif ($method === 'support_code')
            <h2 class="text-sm font-bold text-navy-800 mb-3">Reset with Support Code</h2>
            <div class="mb-3 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
                Contact support and provide your <strong>Hardware ID</strong>. They will give you a code valid for 1 hour.
                <p class="mt-1 font-mono text-xs select-all">{{ app(\App\Services\HardwareFingerprint::class)->generate() }}</p>
            </div>
            @if ($error)
                <div class="mb-3 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $error }}</div>
            @endif
            <form wire:submit="resetWithSupportCode" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Support Code</label>
                    <input wire:model="support_code" type="text" placeholder="12-character code" autofocus
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                    <input wire:model="new_password" type="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('new_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input wire:model="new_password_confirmation" type="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('method', '')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Back</button>
                    <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Reset Password</button>
                </div>
            </form>
        @endif

        @if (!$success)
            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-navy-600">Back to Login</a>
            </div>
        @endif
    </div>
</div>
