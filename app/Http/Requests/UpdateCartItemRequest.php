<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Spec ref: §20.6 — PATCH /api/cart/{productId} request (R: qty).
 * Optional `variant_id` (not in the spec) to disambiguate when the
 * same product is in the cart with more than one variant — see
 * CartController::update() for why.
 */
class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qty' => ['required', 'integer', 'min:1'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];
    }
}
