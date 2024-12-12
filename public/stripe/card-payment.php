<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/stripe.example.php';

try {
    $stripeConfig = [
        'secret_key' => $config['secret_key'],
        'publishable_key' => $config['publishable_key'],
        'webhook_secret' => $config['webhook_secret'],
        'success_url' => $config['success_url'],
        'cancel_url' => $config['cancel_url'],
    ];

    $stripeGateway = PaymentGatewayFactory::create('stripe', $stripeConfig);

    $paymentData = [
        'amount' => 100.00,
        'currency' => 'USD',
        'payment_method' => 'card',
        'customerInfo' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'description' => 'Test payment for John Doe',
        ],
    ];

    $result = $stripeGateway->createPayment($paymentData);
    print_r(json_encode($result));

} catch (PaymentGatewayException $e) {
    print_r($e->toJson());
}
