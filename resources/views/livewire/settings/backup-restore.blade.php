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
            <button wire:click="createBackup" class="px-5 py-2 bg-navy-600 hover:bg-navy-500 text-white text-sm font-medium rounded-lg transition-colors">
                Backup Now
            </button>
        </div>

        {{-- Restore Confirmation --}}
        @if ($showRestoreConfirm)
            <div class="mb-6 bg-red-50 border-2 border-red-300 rounded-lg px-6 py-5">
                <h3 class="text-sm font-bold text-red-800 mb-2">Confirm Database Restore</h3>
                <p class="text-sm text-red-700 mb-3">This will <strong>replace your current database and files</strong> with the backup. A safety copy will be saved first.</p>
                <div class="flex gap-2">
                    <button wire:click="restore" class="px-5 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-medium rounded-lg">Yes, Restore</button>
                    <button wire:click="$set('showRestoreConfirm', false)" class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg">Cancel</button>
                </div>
            </div>
        @endif

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
                                <button wire:click="deleteBackup('{{ addslashes($backup['path']) }}')" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No backups yet. Create your first backup or wait for auto-backup.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
