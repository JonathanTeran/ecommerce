<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_company' => 'nullable|string|max:255',
            'customer_notes' => 'nullable|string|max:2000',
            'shipping_address.name' => 'required|string|max:255',
            'shipping_address.address' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:100',
            'shipping_address.zip' => 'required|string|max:20',
            'shipping_address.phone' => 'required|string|max:20',
            'billing_address' => 'nullable|array',
            'billing_address.name' => 'nullable|string|max:255',
            'billing_address.tax_id' => 'nullable|string|max:13',
            'billing_address.address' => 'nullable|string|max:255',
            'billing_address.city' => 'nullable|string|max:100',
            'billing_address.state' => 'nullable|string|max:100',
            'billing_address.zip' => 'nullable|string|max:20',
            'billing_address.phone' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'El nombre es obligatorio.',
            'customer_email.required' => 'El correo electronico es obligatorio.',
            'customer_phone.required' => 'El telefono es obligatorio.',
        ];
    }
}
