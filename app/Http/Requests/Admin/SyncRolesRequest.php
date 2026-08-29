<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'present' (not 'required') so an explicit empty array
            // ("roles": []) is valid — that's how an admin strips
            // someone down to zero roles via this endpoint.
            'roles' => ['present', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'slug')],
        ];
    }
}
