<?php

namespace App\Filament\Widgets;

use App\Models\Institution;
use App\Models\Subscription;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SubscriptionAlertWidget extends Widget
{
    protected static string $view = 'filament.widgets.subscription-alert-widget';
    protected static ?int $sort = -1; // Show at the top of the dashboard

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $isSuperAdmin = $user->hasRole('super_admin');
        
        $pendingInstitutions = [];
        $myInstitution = null;
        $expiringSoon = false;
        $daysLeft = 0;

        if ($isSuperAdmin) {
            // Fetch institutions that are pending renewal, pending cancellation, or expiring in 15 days
            $pendingInstitutions = Institution::with('subscription')
                ->whereIn('subscription_status', ['pending_renewal', 'pending_cancellation'])
                ->orWhere(function ($query) {
                    $query->whereNotNull('subscription_expires_at')
                        ->where('subscription_expires_at', '<=', now()->addDays(15));
                })
                ->get();
        } else {
            $myInstitution = $user->institution;
            if ($myInstitution && $myInstitution->subscription_expires_at) {
                $daysLeft = (int) now()->diffInDays($myInstitution->subscription_expires_at, false);
                $expiringSoon = $daysLeft <= 15;
            }
        }

        return [
            'isSuperAdmin' => $isSuperAdmin,
            'pendingInstitutions' => $pendingInstitutions,
            'myInstitution' => $myInstitution,
            'expiringSoon' => $expiringSoon,
            'daysLeft' => $daysLeft,
        ];
    }

    public function requestRenewal(int $institutionId)
    {
        $institution = Institution::find($institutionId);
        if ($institution) {
            $institution->update(['subscription_status' => 'pending_renewal']);
            
            Notification::make()
                ->title('Renewal request submitted')
                ->body('Your request for subscription renewal has been sent to the Super Admin.')
                ->success()
                ->send();
        }
    }

    public function requestCancellation(int $institutionId)
    {
        $institution = Institution::find($institutionId);
        if ($institution) {
            $institution->update(['subscription_status' => 'pending_cancellation']);
            
            Notification::make()
                ->title('Cancellation request submitted')
                ->body('Your request to end subscription has been sent to the Super Admin.')
                ->warning()
                ->send();
        }
    }

    public function approveRenewal(int $institutionId)
    {
        $institution = Institution::find($institutionId);
        if ($institution && $institution->subscription) {
            $plan = $institution->subscription;
            $currentExpiry = $institution->subscription_expires_at ?: now();
            
            // Extend based on billing cycle
            $newExpiry = match ($plan->billing_cycle) {
                'monthly' => $currentExpiry->addMonth(),
                '6_months' => $currentExpiry->addMonths(6),
                'yearly' => $currentExpiry->addYear(),
                default => $currentExpiry->addMonth(),
            };

            $institution->update([
                'subscription_expires_at' => $newExpiry,
                'subscription_status' => 'active',
            ]);

            Notification::make()
                ->title('Renewal Approved')
                ->body("Successfully renewed subscription for {$institution->name} until " . $newExpiry->format('d M Y') . '.')
                ->success()
                ->send();
        }
    }

    public function approveCancellation(int $institutionId)
    {
        $institution = Institution::find($institutionId);
        if ($institution) {
            $institution->update([
                'subscription_id' => null,
                'subscription_expires_at' => null,
                'subscription_status' => 'active', // reset status
            ]);

            Notification::make()
                ->title('Subscription Ended')
                ->body("Subscription for {$institution->name} has been successfully ended.")
                ->danger()
                ->send();
        }
    }
}
