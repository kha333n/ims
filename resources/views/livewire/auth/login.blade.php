<div class="w-full max-w-sm mx-4">
    <div class="text-center mb-8">
        <img src="{{ asset('icon.png') }}" alt="IMS" class="w-20 h-20 mx-auto mb-3 rounded-xl">
        <h1 class="text-2xl font-bold text-white">IMS</h1>
        <p class="text-sm text-gray-400">Installment Management System</p>
    </div>

    <div class="bg-white rounded-xl shadow-2xl px-6 py-6">
        @if ($error)
            <div class="mb-4 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $error }}</div>
        @endif

        <form wire:submit="login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input wire:model="username" type="text" autofocus
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input wire:model="password" type="password"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 focus:border-navy-400 outline-none">
                @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">
                Login
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('password.reset') }}" class="text-xs text-gray-500 hover:text-navy-600">Forgot password?</a>
        </div>
    </div>
</div>
