<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
 
        {{-- Welcome card --}}
        <div class="col-span-1 md:col-span-3 rounded-xl bg-white dark:bg-gray-800 p-6 shadow">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                Welcome back, {{ auth()->user()->name }} 👋
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                You are logged in as
                <span class="font-medium text-primary-600">
                    {{ auth()->user()->roles->pluck('name')->join(', ') }}
                </span>
            </p>
        </div>
 
    </div>
</x-filament-panels::page>
