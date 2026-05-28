<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManageBackups extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Database Backups';
    protected static ?string $title           = 'Database & Backup Controls';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.pages.manage-backups';

    // ── Livewire state ─────────────────────────────────────────────────

    public bool   $isRunning = false;
    public string $lastOutput = '';

    // ── Read backup files from disk ────────────────────────────────────

    public function getBackupFiles(): array
    {
        $appName = config('app.name', 'Laravel');
        $disk    = Storage::disk('local');
        $files   = [];

        // Spatie backup stores files inside a folder named after your app
        if ($disk->exists($appName)) {
            foreach ($disk->files($appName) as $file) {
                $files[] = [
                    'name'       => basename($file),
                    'path'       => $file,
                    'size'       => $this->formatBytes($disk->size($file)),
                    'size_raw'   => $disk->size($file),
                    'created_at' => date('d M Y, h:i A', $disk->lastModified($file)),
                    'timestamp'  => $disk->lastModified($file),
                ];
            }
        }

        // Sort newest first
        usort($files, fn ($a, $b) => $b['timestamp'] - $a['timestamp']);

        return $files;
    }

    // ── Actions ────────────────────────────────────────────────────────

    public function runFullBackup(): void
    {
        $this->isRunning = true;

        try {
            Artisan::call('backup:run');
            $this->lastOutput = Artisan::output();

            Notification::make()
                ->title('Full backup completed successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Backup failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isRunning = false;
    }

    public function runDbBackup(): void
    {
        try {
            Artisan::call('backup:run --only-db');
            $this->lastOutput = Artisan::output();

            Notification::make()
                ->title('Database backup completed successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Database backup failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cleanBackups(): void
    {
        try {
            Artisan::call('backup:clean');
            $this->lastOutput = Artisan::output();

            Notification::make()
                ->title('Old backups cleaned successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Clean failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteBackup(string $path): void
    {
        try {
            Storage::disk('local')->delete($path);

            Notification::make()
                ->title('Backup deleted')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Delete failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadBackup(string $path): ?BinaryFileResponse
    {
        // Build the absolute path on the server filesystem
        $fullPath = storage_path('app/' . $path);

        // Check it actually exists before trying to download
        if (! file_exists($fullPath)) {
            Notification::make()
                ->title('File not found')
                ->body('The backup file no longer exists on disk.')
                ->danger()
                ->send();

            return null;
        }

        return response()->download(
            $fullPath,
            basename($path)  // filename shown to the user when downloading
        );
    }

    // ── Helper ─────────────────────────────────────────────────────────

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)   return round($bytes / 1048576, 2)   . ' MB';
        if ($bytes >= 1024)      return round($bytes / 1024, 2)      . ' KB';
        return $bytes . ' B';
    }

    // ── Page header actions ────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runFullBackup')
                ->label('Full Backup')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('This will backup your full database and files.')
                ->action(fn () => $this->runFullBackup()),

            Action::make('runDbBackup')
                ->label('Database Only')
                ->icon('heroicon-o-circle-stack')
                ->color('info')
                ->requiresConfirmation()
                ->modalDescription('This will backup the database only.')
                ->action(fn () => $this->runDbBackup()),

            Action::make('cleanBackups')
                ->label('Clean Old Backups')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Removes backups older than your retention policy.')
                ->action(fn () => $this->cleanBackups()),
        ];
    }
}
