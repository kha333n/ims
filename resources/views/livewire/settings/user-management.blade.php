<div>
    <div class="max-w-5xl mx-auto">
        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 {{ str_contains($actionSummary['action'], 'Error') ? 'border-red-500' : 'border-green-500' }} px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium {{ str_contains($actionSummary['action'], 'Error') ? 'text-red-700' : 'text-green-700' }}">{{ $actionSummary['action'] }}: {{ $actionSummary['name'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-navy-800">User Management</h1>
            <div class="relative w-72">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search users..."
                       class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-navy-800 text-white">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-medium">Username</th>
                        <th class="px-4 py-2.5 text-left font-medium">Name</th>
                        <th class="px-4 py-2.5 text-center font-medium">Role</th>
                        <th class="px-4 py-2.5 text-left font-medium">Linked Employee</th>
                        <th class="px-4 py-2.5 text-center font-medium">Status</th>
                        <th class="px-4 py-2.5 text-left font-medium">Last Login</th>
                        <th class="px-4 py-2.5 text-center font-medium w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-blue-50 transition-colors {{ !$user->is_active ? 'opacity-50' : '' }}" wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-2 font-mono text-sm">{{ $user->username }}</td>
                            <td class="px-4 py-2 font-medium text-navy-800">{{ $user->name }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                    {{ $user->role === 'owner' ? 'bg-purple-100 text-purple-700' : ($user->role === 'sale_man' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $user->employee?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-center">
                                @if ($user->is_active)
                                    <span class="text-green-600 text-xs font-medium">Active</span>
                                @else
                                    <span class="text-red-500 text-xs font-medium">Blocked</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-2 text-center space-x-1">
                                <button wire:click="openEditModal({{ $user->id }})" class="text-navy-500 hover:text-navy-700 text-xs font-medium">Edit</button>
                                <button wire:click="toggleActive({{ $user->id }})" class="text-xs font-medium {{ $user->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700' }}">
                                    {{ $user->is_active ? 'Block' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[85vh] flex flex-col">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-navy-800">Edit User</h2>
                </div>
                <form wire:submit="saveUser" class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                            <input wire:model="edit_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            @error('edit_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                            <input wire:model="edit_username" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            @error('edit_username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">New Password <span class="text-gray-400">(leave blank to keep)</span></label>
                            <input wire:model="edit_password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                            @error('edit_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                            <select wire:model.live="edit_role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-navy-400 outline-none">
                                <option value="owner">Owner</option>
                                <option value="sale_man">Sale Man</option>
                                <option value="recovery_man">Recovery Man</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Permissions</label>
                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                            @foreach ($allPermissions as $perm)
                                <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-gray-50 cursor-pointer">
                                    <input wire:model="edit_permissions.{{ $perm->name }}" type="checkbox" class="rounded border-gray-300 text-navy-600 focus:ring-navy-400">
                                    <span>{{ str_replace('.', ' — ', $perm->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-navy-600 hover:bg-navy-500 rounded-lg transition-colors disabled:opacity-50"><svg wire:loading wire:target="saveUser" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
