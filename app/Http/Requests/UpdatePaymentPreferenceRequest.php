<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Spec ref: §20.2 — PUT /api/account/payment-preferences (R: preferred_payment)
 * Values per spec §6: cod, aba, wing, khqr.
 */
class UpdatePaymentPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preferred_payment' => ['required', 'string', 'in:cod,aba,wing,khqr'],
        ];
    }
}
