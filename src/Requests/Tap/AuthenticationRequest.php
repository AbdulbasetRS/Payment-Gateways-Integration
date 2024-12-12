<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Tap;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class AuthenticationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'secret_key' => ['required', 'string'],
            'publishable_key' => ['required', 'string'],
            'success_url' => ['required', 'string'],
            'cancel_url' => ['required', 'string']
        ];
    }
}