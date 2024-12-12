<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Paypal;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class AuthenticationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'mode' => ['required', 'string'],
            'success_url' => ['required', 'string'],
            'cancel_url' => ['required', 'string']
        ];
    }
}