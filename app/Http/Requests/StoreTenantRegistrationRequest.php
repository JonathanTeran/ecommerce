<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreTenantRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:tenant_registrations,owner_email|unique:users,email',
            'owner_phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'country' => 'required|string|max:100',
            'accepted_terms' => 'accepted',
        ];
    }
}
