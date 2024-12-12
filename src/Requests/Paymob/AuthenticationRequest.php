<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Paymob;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class AuthenticationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'api_key' => ['required', 'string'],
        ];
    }
}
