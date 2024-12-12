<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/paypal.example.php';

try {
    $paypalConfig = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'mode' => $config['mode'],
        'success_url' => $config['success_url'],
        'cancel_url' => $config['cancel_url'],
    ];

    $paypalGateway = PaymentGatewayFactory::create('paypal', $paypalConfig);

    $paymentData = [
        'amount' => 100.00,
        'currency' => 'USD',
        'payment_method' => 'PAYPAL',
        'description' => 'Test payment',
    ];

    $result = $paypalGateway->createPayment($paymentData);
    echo '<pre>';
    print_r(json_encode($result));
    echo '</pre>';

} catch (PaymentGatewayException $e) {
    print_r($e->toJson());
}
