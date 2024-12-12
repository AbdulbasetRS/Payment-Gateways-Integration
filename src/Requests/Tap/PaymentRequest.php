<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Tap;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'positive'],
            'currency' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'billing_data' => ['required', 'array'],
            'billing_data.*.first_name' => ['required', 'string'],
            'billing_data.*.last_name' => ['required', 'string'],
            'billing_data.*.email' => ['required', 'email'],
            'billing_data.*.phone_number' => ['required', 'phone']
        ];
    }
}