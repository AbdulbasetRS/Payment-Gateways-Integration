<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Stripe;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'positive'],
            'currency' => ['required', 'string', 'in:USD,EUR,GBP'],
            'payment_method' => ['required', 'string'],
            'customerInfo' => ['required', 'array'],
            'customerInfo.*.name' => ['required', 'string'],
            'customerInfo.*.email' => ['required', 'email'],
            'customerInfo.*.phone' => ['required', 'phone'],
            'customerInfo.*.description' => ['required', 'string'],
        ];
    }
}
