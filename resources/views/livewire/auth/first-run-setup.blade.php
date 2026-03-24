<div class="w-full max-w-md mx-4">
    <div class="text-center mb-8">
        <img src="{{ asset('icon.png') }}" alt="IMS" class="w-20 h-20 mx-auto mb-3 rounded-xl">
        <h1 class="text-2xl font-bold text-white">Welcome to IMS</h1>
        <p class="text-sm text-gray-400">Set up your administrator account</p>
    </div>

    <div class="bg-white rounded-xl shadow-2xl px-6 py-6">
        @if ($recoveryKey)
            {{-- Recovery Key Display --}}
            <div class="text-center space-y-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-navy-800">Account Created!</h2>
                <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg px-4 py-4">
                    <p class="text-xs font-bold text-yellow-800 uppercase mb-2">Recovery Key — Write This Down!</p>
                    <p class="font-mono text-lg font-bold text-navy-800 tracking-wider select-all">{{ $recoveryKey }}</p>
                    <p class="text-xs text-yellow-700 mt-2">This is your only way to reset the password if you forget it. Store it safely. It will NOT be shown again.</p>
                </div>
                <button wire:click="continueToApp" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors">
                    I've Saved My Key — Continue
                </button>
            </div>
        @else
            {{-- Setup Form --}}
            <form wire:submit="setup" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" autofocus
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input wire:model="username" type="text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input wire:model="password" type="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <input wire:model="password_confirmation" type="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">
                    Create Account
                </button>
            </form>
        @endif
    </div>
</div>
