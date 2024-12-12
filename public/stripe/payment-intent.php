<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/stripe.example.php';

try {
    $stripeConfig = [
        'secret_key' => $config['secret_key'],
        'publishable_key' => $config['publishable_key'],
        'success_url' => $config['success_url'],
        'cancel_url' => $config['cancel_url'],
    ];

    $stripeGateway = PaymentGatewayFactory::create('stripe', $stripeConfig);

    $paymentData = [
        'amount' => 50.00,
        'currency' => 'usd',
        'payment_method' => 'payment_intent',
        'customerInfo' => [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'description' => 'Payment for service XYZ',
        ],
    ];

    $result = $stripeGateway->createPayment($paymentData);
    print_r(json_encode($result));

} catch (PaymentGatewayException $e) {
    print_r($e->toJson());
}
