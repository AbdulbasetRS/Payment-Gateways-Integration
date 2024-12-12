<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/paymob.example.php';

$api_key = '' . $config['api_key'];
$username = $config['username'];
$password = $config['password'];
$mobile_wallets = $config['mobile_wallets'];
$online_card = $config['online_card'];

// Get Transaction ID from query parameters
$transaction_id = $_GET['transaction_id'] ?? null; // transaction_id

if (!$transaction_id) {
    die('Transaction ID is required');
}

if ($transaction_id) {
    try {
        $config = [
            'api_key' => $api_key,
            'integration_id' => $online_card,
        ];

        $paymentGateway = PaymentGatewayFactory::create('paymob', $config);
        $result = $paymentGateway->verifyPayment($transaction_id);

        echo '<pre>';
        print_r(json_encode($result));
        echo '</pre>';
    } catch (PaymentGatewayException $e) {
        print_r($e->toJson());
    }
} else {
    echo 'Invalid callback request';
}
