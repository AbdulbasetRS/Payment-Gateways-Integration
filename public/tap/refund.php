<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/tap.example.php';

$paymentId = $_GET['tap_id'] ?? null;

if (!$paymentId) {
    die('Payment ID is required');
}

try {
    $tapConfig = [
        'secret_key' => $config['secret_key'],
        'publishable_key' => $config['publishable_key'],
        'success_url' => $config['success_url'],
        'cancel_url' => $config['cancel_url'],
    ];

    $tapGateway = PaymentGatewayFactory::create('tap', $tapConfig);
    $result = $tapGateway->refund($paymentId, 100.00);

    echo '<pre>';
    print_r(json_encode($result));
    echo '</pre>';

} catch (PaymentGatewayException $e) {
    print_r($e->toJson());
}