<x-filament-panels::page>

    <div class="space-y-6">

        {{-- Step 1: Select User --}}
        <x-filament::section>
            <x-slot name="heading">Step 1 — Select a User</x-slot>
            <x-slot name="description">Choose which user you want to manage roles and permissions for.</x-slot>

            <div class="max-w-lg">
                <select
                    wire:model.live="selectedUserId"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800 text-sm px-3 py-2
                           focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                >
                    <option value="">— Select a user —</option>
                    @foreach($this->getAllUsers() as $user)
                        <option value="{{ $user['id'] }}">
                            {{ $user['label'] }}
                            @if($user['roles']) [{{ $user['roles'] }}] @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </x-filament::section>

        @if($this->getSelectedUser())

        @php $user = $this->getSelectedUser(); @endphp

        {{-- Selected user info card --}}
        <div class="flex items-center gap-4 p-4 rounded-xl border border-amber-500/30 bg-amber-500/5">
            <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center
                        text-amber-600 font-semibold text-sm">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-xs text-gray-500">{{ $user->email }}</p>
            </div>
            <div class="ml-auto flex gap-2">
                @foreach($user->roles as $role)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Step 2: Assign Roles --}}
        <x-filament::section>
            <x-slot name="heading">Step 2 — Assign Roles</x-slot>
            <x-slot name="description">Select which roles this user should have. This replaces all existing roles.</x-slot>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                @foreach($this->getAllRoles() as $role)
                <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all
                    {{ in_array($role->id, $selectedRoles)
                        ? 'border-amber-500 bg-amber-500/10'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300' }}">
                    <input
                        type="checkbox"
                        wire:model.live="selectedRoles"
                        value="{{ $role->id }}"
                        class="rounded border-gray-300 text-amber-500 focus:ring-amber-500"
                        {{ $role->name === 'super_admin' ? 'disabled' : '' }}
                    >
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $role->permissions_count ?? $role->permissions->count() }} permissions</p>
                    </div>
                </label>
                @endforeach
            </div>

            <x-filament::button wire:click="syncRoles" color="primary" icon="heroicon-m-shield-check">
                Save Roles
            </x-filament::button>
        </x-filament::section>

        {{-- Step 3: Assign Direct Permissions --}}
        <x-filament::section>
            <x-slot name="heading">Step 3 — Assign Direct Permissions</x-slot>
            <x-slot name="description">
                Grant specific permissions directly to this user — on top of their role permissions.
            </x-slot>

            @foreach($this->getAllPermissions() as $group => $permissions)
            <div class="mb-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-3">
                    {{ $group }}
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($permissions as $permission)
                    <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer text-sm transition-all
                        {{ in_array($permission->id, $selectedPermissions)
                            ? 'border-amber-500 bg-amber-500/10 text-amber-700 dark:text-amber-400'
                            : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:border-gray-300' }}">
                        <input
                            type="checkbox"
                            wire:model.live="selectedPermissions"
                            value="{{ $permission->id }}"
                            class="rounded border-gray-300 text-amber-500 focus:ring-amber-500"
                        >
                        <span class="font-mono text-xs">{{ $permission->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach

            <x-filament::button wire:click="syncPermissions" color="primary" icon="heroicon-m-key">
                Save Permissions
            </x-filament::button>
        </x-filament::section>

        {{-- Danger Zone --}}
        <x-filament::section>
            <x-slot name="heading">Danger Zone</x-slot>
            <x-slot name="description">Revoke all roles and permissions from this user instantly.</x-slot>

            <x-filament::button
                wire:click="revokeAll"
                color="danger"
                icon="heroicon-m-x-circle"
                wire:confirm="Are you sure? This removes ALL roles and permissions from {{ $user->name }}."
            >
                Revoke Everything from {{ $user->name }}
            </x-filament::button>
        </x-filament::section>

        @else

        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <x-heroicon-o-shield-check class="w-7 h-7 text-gray-400"/>
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">No user selected</p>
            <p class="text-xs text-gray-500 mt-1">Select a user above to manage their roles and permissions.</p>
        </div>

        @endif

    </div>

</x-filament-panels::page>