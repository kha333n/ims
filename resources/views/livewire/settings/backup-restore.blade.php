<div>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Backup & Restore</h1>

        @if ($actionSummary)
            <div class="mb-4 bg-white rounded-lg shadow border-l-4 border-green-500 px-6 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-green-700">{{ $actionSummary['action'] }}: {{ $actionSummary['detail'] }}</span>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow px-6 py-5 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-navy-800">Create Backup</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Creates a copy of the current database</p>
                </div>
                <button wire:click="createBackup" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">Create Backup</button>
            </div>
            @error('backup') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-lg shadow px-6 py-5">
            <h2 class="text-sm font-bold text-navy-800 mb-3">Local Backups</h2>
            @if (count($backups) > 0)
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left font-medium text-gray-600">File</th><th class="px-3 py-2 text-left font-medium text-gray-600">Date</th><th class="px-3 py-2 text-right font-medium text-gray-600">Size</th><th class="px-3 py-2 w-16"></th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($backups as $backup)
                            <tr>
                                <td class="px-3 py-2 font-medium text-navy-800">{{ $backup['name'] }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $backup['date'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600">{{ $backup['size'] }} KB</td>
                                <td class="px-3 py-2 text-center"><button wire:click="deleteBackup('{{ $backup['name'] }}')" wire:confirm="Delete this backup?" class="text-red-500 hover:text-red-700 text-xs">Delete</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-gray-400">No backups yet. Create one above.</p>
            @endif
        </div>
    </div>
</div>
