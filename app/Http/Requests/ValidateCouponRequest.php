<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest or Auth
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
