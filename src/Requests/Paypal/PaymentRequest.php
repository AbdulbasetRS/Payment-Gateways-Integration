<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Requests\Paypal;

use Abdulbaset\PaymentGatewaysIntegration\Requests\FormRequest;

class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'positive'],
            'currency' => ['required', 'string', 'in:USD,EUR,GBP'],
            'description' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', 'in:PAYPAL'], // PAYPAL,CREDIT_CARD,BANK,CARRIER,ALTERNATE_PAYMENT,PAY_UPON_INVOICE
        ];
    }
}
