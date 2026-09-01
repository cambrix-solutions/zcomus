<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Spec ref: §20.17 — "POST request (R): shop_id (or vendor_id)".
 * Normalizes vendor_id -> shop_id before validation so the controller
 * only ever deals with one field name.
 */
class FollowShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('shop_id') && $this->has('vendor_id')) {
            $this->merge(['shop_id' => $this->input('vendor_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
        ];
    }
}
