<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Gated by 'auth:admin' at the route level.
        return true;
    }

    public function rules(): array
    {
        return [
            // Checked against the roles table itself rather than a
            // hardcoded list, so adding a 4th role later doesn't
            // require touching this file.
            'role' => ['required', 'string', Rule::exists('roles', 'slug')],
        ];
    }
}
