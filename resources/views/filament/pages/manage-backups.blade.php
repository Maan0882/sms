<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Stats row --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            <div class="rounded-xl border border-gray-200 dark:border-gray-700
                        bg-white dark:bg-gray-800 p-4">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Total Backups</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ count($this->getBackupFiles()) }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700
                        bg-white dark:bg-gray-800 p-4">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Total Size</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    @php
                        $total = collect($this->getBackupFiles())->sum('size_raw');
                        echo $total >= 1048576
                            ? round($total / 1048576, 2) . ' MB'
                            : round($total / 1024, 2) . ' KB';
                    @endphp
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700
                        bg-white dark:bg-gray-800 p-4">
                <p class="text-xs text-gray-500 font-mono uppercase tracking-wider">Latest Backup</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                    {{ collect($this->getBackupFiles())->first()['created_at'] ?? 'No backups yet' }}
                </p>
            </div>

        </div>

        {{-- Backup files table --}}
        <x-filament::section>
            <x-slot name="heading">Available Backup Files</x-slot>

            @php $files = $this->getBackupFiles(); @endphp

            @if(count($files) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($files as $file)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">

                            {{-- File name --}}
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-archive-box class="w-4 h-4 text-gray-400 flex-shrink-0"/>
                                    <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                        {{ $file['name'] }}
                                    </span>
                                </div>
                            </td>

                            {{-- Size --}}
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs
                                            bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                            font-mono">
                                    {{ $file['size'] }}
                                </span>
                            </td>

                            {{-- Created at --}}
                            <td class="py-3 px-4 text-xs text-gray-500">
                                {{ $file['created_at'] }}
                            </td>

                            {{-- Actions --}}
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- Download --}}
                                    <x-filament::button
                                        size="xs"
                                        color="success"
                                        icon="heroicon-m-arrow-down-tray"
                                        wire:click="downloadBackup('{{ $file['path'] }}')"
                                    >
                                        Download
                                    </x-filament::button>

                                    {{-- Delete --}}
                                    <x-filament::button
                                        size="xs"
                                        color="danger"
                                        icon="heroicon-m-trash"
                                        wire:click="deleteBackup('{{ $file['path'] }}')"
                                        wire:confirm="Delete this backup file permanently?"
                                    >
                                        Delete
                                    </x-filament::button>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @else

            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <x-heroicon-o-circle-stack class="w-12 h-12 text-gray-300 mb-4"/>
                <p class="text-sm font-medium text-gray-900 dark:text-white">No backups yet</p>
                <p class="text-xs text-gray-500 mt-1">
                    Click "Full Backup" or "Database Only" above to create your first backup.
                </p>
            </div>

            @endif

        </x-filament::section>

        {{-- Last command output --}}
        @if($this->lastOutput)
        <x-filament::section>
            <x-slot name="heading">Last Command Output</x-slot>
            <pre class="text-xs font-mono bg-gray-900 text-green-400 rounded-lg p-4 overflow-x-auto whitespace-pre-wrap">{{ $this->lastOutput }}</pre>
        </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
