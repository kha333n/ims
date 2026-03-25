<div>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-xl font-bold text-navy-800 mb-4">Backup & Restore</h1>

        @if ($actionSummary)
            <div class="mb-6 bg-white rounded-lg shadow border-l-4 {{ $actionSummary['type'] === 'success' ? 'border-green-500' : 'border-red-500' }} px-6 py-5">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-lg font-bold {{ $actionSummary['type'] === 'success' ? 'text-green-700' : 'text-red-700' }}">{{ $actionSummary['title'] }}</h2>
                    <button wire:click="$set('actionSummary', null)" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <p class="text-sm text-gray-600">{{ $actionSummary['message'] }}</p>
            </div>
        @endif

        {{-- Auto-Backup Status --}}
        <div class="mb-6 bg-white rounded-lg shadow px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-navy-800">Auto-Backup</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Automatic backup runs every 12 hours.
                        @if ($lastBackup)
                            Last backup: <span class="font-medium {{ $isOverdue ? 'text-orange-600' : 'text-green-600' }}">{{ \Carbon\Carbon::parse($lastBackup)->diffForHumans() }}</span>
                        @else
                            <span class="text-orange-600 font-medium">No backup yet.</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($isOverdue)
                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded">Overdue</span>
                    @else
                        <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">Up to date</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Create Backup --}}
        <div class="bg-white rounded-lg shadow px-6 py-5 mb-6">
            <h2 class="text-sm font-bold text-navy-800 mb-2">Manual Backup</h2>
            <p class="text-sm text-gray-600 mb-3">Creates an encrypted backup (.imsb) containing your database and all files. Only this app with your license can restore it.</p>
            <button wire:click="createBackup" wire:loading.attr="disabled" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                <svg wire:loading wire:target="createBackup" class="animate-spin -ml-1 mr-2 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Backup Now
            </button>
        </div>

        {{-- Restore Confirmation is shown as a modal below --}}

        {{-- Local Backups --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-sm font-bold text-navy-800">Local Backups</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">File</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Size</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="px-4 py-2 font-mono text-xs">{{ $backup['filename'] }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ $backup['size_formatted'] }}</td>
                            <td class="px-4 py-2">{{ $backup['created_at'] }}</td>
                            <td class="px-4 py-2 text-right">
                                <button wire:click="confirmRestore('{{ addslashes($backup['path']) }}')" class="text-navy-600 hover:text-navy-800 text-xs font-medium mr-2">Restore</button>
                                <button wire:click="confirmDeleteBackup('{{ addslashes($backup['path']) }}')" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No backups yet. Create your first backup or wait for auto-backup.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showRestoreConfirm)
        <x-confirm-dialog
            title="Restore Database"
            message="This will REPLACE your current database and all files with the selected backup. A safety copy will be saved first. This cannot be undone."
            confirm-label="Yes, Restore Now"
            wire-confirm="restore"
            wire-cancel="$set('showRestoreConfirm', false)"
            variant="danger"
        />
    @endif

    @if ($showDeleteConfirm)
        <x-confirm-dialog
            title="Delete Backup"
            message="Are you sure you want to permanently delete this backup file? This cannot be undone."
            confirm-label="Delete"
            wire-confirm="deleteBackup"
            wire-cancel="cancelDeleteBackup"
            variant="danger"
        />
    @endif
</div>
