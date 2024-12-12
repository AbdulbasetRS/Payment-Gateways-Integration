<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/stripe.example.php';

// Payment order ID to verify
$paymentIntentId = 'cs_test_XXXXXXXXXXXX'; // Replace with actual payment order ID

try {
    $stripeConfig = [
        'secret_key' => $config['secret_key'],
        'publishable_key' => $config['publishable_key'],
        'success_url' => $config['success_url'],
        'cancel_url' => $config['cancel_url'],
    ];

    $stripeGateway = PaymentGatewayFactory::create('stripe', $stripeConfig);
    $result = $stripeGateway->verifyPayment($paymentIntentId);

    print_r(json_encode($result));

} catch (PaymentGatewayException $e) {
    print_r($e->toJson());
}
