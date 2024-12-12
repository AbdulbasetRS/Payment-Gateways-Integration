<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Paymob;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'billing_data' => ['required', 'array'],
            'billing_data.*.first_name' => ['required', 'string'],
            'billing_data.*.last_name' => ['required', 'string'],
            'billing_data.*.email' => ['required', 'email'],
            'billing_data.*.phone_number' => ['required', 'string', 'phone'],
            'billing_data.*.apartment' => ['required', 'string'],
            'billing_data.*.building' => ['required', 'string'],
            'billing_data.*.street' => ['required', 'string'],
            'billing_data.*.floor' => ['required', 'string'],
            'billing_data.*.city' => ['required', 'string'],
            'billing_data.*.state' => ['required', 'string'],
            'billing_data.*.country' => ['required', 'string'],
        ];
    }
}
