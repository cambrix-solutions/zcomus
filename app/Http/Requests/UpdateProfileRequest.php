<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Spec ref: §20.2 — PUT /api/profile request (name R, email R, phone O)
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Already gated by 'auth:web' — every authenticated role may edit their own profile.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
