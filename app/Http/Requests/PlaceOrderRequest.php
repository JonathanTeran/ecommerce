<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
            'shipping_rate_key' => 'nullable|string|max:100',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'shipping_address.name' => 'required|string|max:255',
            'shipping_address.identity_document' => 'required|string|min:10|max:13',
            'shipping_address.email' => 'required|email|max:255',
            'shipping_address.address' => 'required|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:100',
            'shipping_address.zip' => 'required|string|max:20',
            'shipping_address.phone' => 'required|string|max:20',
            'billing_address.name' => 'required|string|max:255',
            'billing_address.tax_id' => 'required|string|min:10|max:13',
            'billing_address.address' => 'required|string|max:255',
            'billing_address.city' => 'required|string|max:100',
            'billing_address.state' => 'required|string|max:100',
            'billing_address.zip' => 'required|string|max:20',
            'billing_address.phone' => 'required|string|max:20',
            'card_holder' => 'nullable|string',
            'card_number' => 'nullable|string',
            'card_expiry' => 'nullable|string',
            'card_cvc' => 'nullable|string',
            'accepted_legal_documents' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'accepted_legal_documents.accepted' => __('Debes aceptar los documentos legales para continuar.'),
        ];
    }
}
