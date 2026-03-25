<div class="w-full max-w-lg mx-4">
    <div class="text-center mb-6">
        <img src="{{ asset('icon.png') }}" alt="IMS" class="w-16 h-16 mx-auto mb-3 rounded-xl">
        <h1 class="text-2xl font-bold text-white">Installment Management System</h1>
        <p class="text-sm text-gray-400 mt-1">Initial Setup</p>
    </div>

    {{-- Step Indicator --}}
    <div class="flex items-center justify-center gap-2 mb-6">
        @foreach (['Company', 'Account', 'Recovery Key', 'License'] as $i => $label)
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $step > $i + 1 ? 'bg-green-500 text-white' : ($step === $i + 1 ? 'bg-white text-navy-800' : 'bg-gray-600 text-gray-400') }}">
                        @if ($step > $i + 1)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="text-xs {{ $step === $i + 1 ? 'text-white font-medium' : 'text-gray-500' }} hidden sm:inline">{{ $label }}</span>
                </div>
                @if ($i < 3)
                    <div class="w-6 h-px {{ $step > $i + 1 ? 'bg-green-500' : 'bg-gray-600' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-2xl px-6 py-6">

        {{-- STEP 1: Company Details --}}
        @if ($step === 1)
            <h2 class="text-lg font-bold text-navy-800 mb-1">Company Details</h2>
            <p class="text-xs text-gray-500 mb-4">This will appear on all reports and printed documents.</p>

            <form wire:submit="saveCompany" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                    <input wire:model="company_name" type="text" autofocus placeholder="e.g. Techmiddle Technologies"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input wire:model="company_address" type="text" placeholder="e.g. Office #5, Commercial Market, Rawalpindi"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('company_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input wire:model="company_phone" type="text" placeholder="e.g. 051-1234567"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('company_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">
                    Next &rarr;
                </button>
            </form>

        {{-- STEP 2: Owner Account --}}
        @elseif ($step === 2)
            <h2 class="text-lg font-bold text-navy-800 mb-1">Owner Account</h2>
            <p class="text-xs text-gray-500 mb-4">Create the administrator account. This will be the main login.</p>

            <form wire:submit="createOwner" class="space-y-4">
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
                <div class="flex gap-3">
                    <button type="button" wire:click="$set('step', 1)" wire:loading.attr="disabled" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50">
                        &larr; Back
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="createOwner">Create Account</span>
                        <span wire:loading wire:target="createOwner" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            @if(config('ims.demo_seed'))
                                Adding demo data, please wait...
                            @else
                                Setting up, please wait...
                            @endif
                        </span>
                    </button>
                </div>
            </form>

        {{-- STEP 3: Recovery Key --}}
        @elseif ($step === 3)
            <div class="text-center space-y-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-navy-800">Account Created!</h2>
                <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg px-4 py-4">
                    <p class="text-xs font-bold text-yellow-800 uppercase mb-2">Recovery Key &mdash; Write This Down!</p>
                    <p class="font-mono text-lg font-bold text-navy-800 tracking-wider select-all">{{ $recoveryKey }}</p>
                    <p class="text-xs text-yellow-700 mt-2">This is your only way to reset the password if you forget it. Store it safely. It will NOT be shown again.</p>
                </div>
                <button wire:click="proceedToLicense" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors">
                    I've Saved My Key &mdash; Continue
                </button>
            </div>

        {{-- STEP 4: License Activation --}}
        @elseif ($step === 4)
            <h2 class="text-lg font-bold text-navy-800 mb-1">Activate License</h2>
            <p class="text-xs text-gray-500 mb-4">Enter your license key to activate the application.</p>

            @if ($licenseResult)
                <div class="mb-4 px-4 py-3 rounded-lg text-sm
                    {{ $licenseResult['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                    {{ $licenseResult['message'] }}
                </div>
            @endif

            @if ($licenseResult && $licenseResult['type'] === 'success')
                <button wire:click="continueToApp" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-500 rounded-lg transition-colors">
                    Start Using IMS &rarr;
                </button>
            @else
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Key <span class="text-red-500">*</span></label>
                        <input wire:model="licenseKey" type="text" placeholder="IMS-XXXX-XXXX-XXXX" autofocus
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-navy-400 outline-none">
                        @error('licenseKey') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <button wire:click="activateLicense" wire:loading.attr="disabled"
                            class="w-full px-4 py-2.5 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50">
                        <svg wire:loading wire:target="activateLicense" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Activate
                    </button>

                    <div class="pt-3 border-t">
                        <p class="text-xs text-gray-500 mb-2">Hardware ID (share with support for license):</p>
                        <p class="font-mono text-xs text-gray-600 bg-gray-50 px-3 py-2 rounded select-all break-all">
                            {{ app(\App\Services\HardwareFingerprint::class)->generate() }}
                        </p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
