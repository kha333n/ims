<div>
    <div class="max-w-lg mx-auto">
        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700">{{ $actionSummary['action'] }}: {{ $actionSummary['detail'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <h1 class="text-xl font-bold text-navy-800 mb-4">My Profile</h1>

        <div class="bg-white rounded-lg shadow px-6 py-5 mb-4">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Profile Information</h2>
            <form wire:submit="updateProfile" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                    <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Username</label>
                    <input type="text" value="{{ auth()->user()->username }}" disabled class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
                    <input type="text" value="{{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}" disabled class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500">
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Update Profile</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow px-6 py-5">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Change Password</h2>
            <form wire:submit="updatePassword" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Current Password</label>
                    <input wire:model="current_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('current_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                    <input wire:model="new_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                    @error('new_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input wire:model="new_password_confirmation" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors">Change Password</button>
            </form>
        </div>
    </div>
</div>
