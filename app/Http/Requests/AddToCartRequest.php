<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Spec ref: §20.6 — POST /api/cart request (R: product_id, qty · O: variant_id)
 */
class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest or Auth — no role check.
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where(fn ($q) => $q->where('product_id', $this->input('product_id'))),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'variant_id.exists' => 'That variant does not belong to the given product.',
        ];
    }
}
