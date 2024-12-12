<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/tap.example.php';

try {
    $tapConfig = [
        'secret_key' => $config['secret_key'],
        'publishable_key' => $config['publishable_key'],
        'success_url' => $config['success_url'],
        'cancel_url' => $config['cancel_url'],
    ];

    $tapGateway = PaymentGatewayFactory::create('tap', $tapConfig);

    $paymentData = [
        'amount' => 100.00,
        'currency' => 'USD',
        'payment_method' => 'card',
        'billing_data' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone_number' => '12345678901',
        ],
    ];

    $result = $tapGateway->createPayment($paymentData);
    echo '<pre>';
    print_r(json_encode($result));
    echo '</pre>';

} catch (PaymentGatewayException $e) {
    print_r($e->toJson());
}
