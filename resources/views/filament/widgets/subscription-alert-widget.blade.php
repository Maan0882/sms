<x-filament-widgets::widget>
    @if ($isSuperAdmin)
        @if (count($pendingInstitutions) > 0)
            <x-filament::section icon="heroicon-o-bell" class="border-danger-500">
                <x-slot name="heading">
                    <span class="text-danger-600 font-bold">Subscription Action Required</span>
                </x-slot>
                
                <div class="space-y-4">
                    @foreach ($pendingInstitutions as $inst)
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $inst->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Plan: <span class="font-medium text-primary-600">{{ $inst->subscription?->plan_name ?? 'No Plan' }}</span>
                                    @if ($inst->subscription_expires_at)
                                        | Expiry: <span class="font-medium">{{ $inst->subscription_expires_at->format('d M Y') }}</span>
                                    @endif
                                </p>
                                <div class="mt-1">
                                    @if ($inst->subscription_status === 'pending_renewal')
                                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-warning-50 dark:bg-warning-500/10 px-2 py-1 text-xs font-medium text-warning-700 dark:text-warning-400 ring-1 ring-inset ring-warning-600/20">
                                            Requested Renewal
                                        </span>
                                    @elseif ($inst->subscription_status === 'pending_cancellation')
                                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-danger-50 dark:bg-danger-500/10 px-2 py-1 text-xs font-medium text-danger-700 dark:text-danger-400 ring-1 ring-inset ring-danger-600/20">
                                            Requested Termination
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-x-1.5 rounded-md bg-gray-50 dark:bg-gray-500/10 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-600/20">
                                            Expiring Soon
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-x-2">
                                @if ($inst->subscription_status === 'pending_renewal')
                                    <x-filament::button wire:click="approveRenewal({{ $inst->id }})" color="success" size="sm">
                                        Approve Renewal
                                    </x-filament::button>
                                @endif
                                @if ($inst->subscription_status === 'pending_cancellation')
                                    <x-filament::button wire:click="approveCancellation({{ $inst->id }})" color="danger" size="sm">
                                        Approve Termination
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    @else
        @if ($myInstitution && ($expiringSoon || in_array($myInstitution->subscription_status, ['pending_renewal', 'pending_cancellation'])))
            <x-filament::section icon="heroicon-o-exclamation-triangle" class="border-warning-500">
                <x-slot name="heading">
                    <span class="text-warning-600 font-bold">Subscription Status Alert</span>
                </x-slot>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        @if ($myInstitution->subscription_status === 'pending_renewal')
                            <p class="text-gray-600 dark:text-gray-300">
                                Your renewal request for subscription plan <strong class="text-primary-600">{{ $myInstitution->subscription?->plan_name }}</strong> is pending approval by the Super Admin.
                            </p>
                        @elseif ($myInstitution->subscription_status === 'pending_cancellation')
                            <p class="text-gray-600 dark:text-gray-300">
                                Your cancellation request for subscription plan <strong class="text-danger-600">{{ $myInstitution->subscription?->plan_name }}</strong> is pending approval by the Super Admin.
                            </p>
                        @else
                            <p class="text-gray-600 dark:text-gray-300">
                                Your subscription plan <strong>{{ $myInstitution->subscription?->plan_name }}</strong> 
                                @if ($daysLeft < 0)
                                    has <span class="text-danger-600 font-semibold">expired</span> on {{ $myInstitution->subscription_expires_at->format('d M Y') }}.
                                @elseif ($daysLeft === 0)
                                    is expiring <span class="text-danger-600 font-semibold">today</span>!
                                @else
                                    will expire in <span class="text-warning-600 font-semibold">{{ $daysLeft }} days</span> on {{ $myInstitution->subscription_expires_at->format('d M Y') }}.
                                @endif
                                Please submit your choice to renew or end your subscription.
                            </p>
                        @endif
                    </div>

                    @if ($myInstitution->subscription_status === 'active')
                        <div class="flex items-center gap-x-2">
                            <x-filament::button wire:click="requestRenewal({{ $myInstitution->id }})" color="success">
                                Renew Subscription
                            </x-filament::button>
                            <x-filament::button wire:click="requestCancellation({{ $myInstitution->id }})" color="danger">
                                End Subscription
                            </x-filament::button>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif
    @endif
</x-filament-widgets::widget>
