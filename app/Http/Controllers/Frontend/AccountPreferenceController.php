<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Http\Requests\UpdatePaymentPreferenceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §3, items 16–19.
 * Same as ProfileController — any authenticated role, always "my own"
 * preferences.
 */
class AccountPreferenceController extends Controller
{
    use ApiResponds;

    /**
     * GET /api/account/notification-preferences
     */
    public function showNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->respond([
            'alert_order' => $user->alert_order,
            'alert_deal' => $user->alert_deal,
            'alert_sms' => $user->alert_sms,
        ]);
    }

    /**
     * PUT /api/account/notification-preferences
     */
    public function updateNotifications(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->respond($user->only(['alert_order', 'alert_deal', 'alert_sms']));
    }

    /**
     * GET /api/account/payment-preferences (Optional in spec)
     */
    public function showPayment(Request $request): JsonResponse
    {
        return $this->respond([
            'preferred_payment' => $request->user()->preferred_payment,
        ]);
    }

    /**
     * PUT /api/account/payment-preferences (Optional in spec)
     */
    public function updatePayment(UpdatePaymentPreferenceRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->respond(['preferred_payment' => $user->preferred_payment]);
    }
}
