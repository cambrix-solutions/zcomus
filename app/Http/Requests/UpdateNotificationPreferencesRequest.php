<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Spec ref: §20.2 — PUT /api/account/notification-preferences (all R, boolean)
 */
class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alert_order' => ['required', 'boolean'],
            'alert_deal' => ['required', 'boolean'],
            'alert_sms' => ['required', 'boolean'],
        ];
    }
}
