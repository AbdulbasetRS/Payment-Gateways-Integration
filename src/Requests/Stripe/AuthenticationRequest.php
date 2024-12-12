<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Stripe;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class AuthenticationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'secret_key' => ['required', 'string'],
            'publishable_key' => ['required', 'string'],
            'webhook_secret' => ['optional', 'nullable'],
            'success_url' => ['required', 'string'],
            'cancel_url' => ['required', 'string']
        ];
    }
}