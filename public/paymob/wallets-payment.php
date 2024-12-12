<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/paymob.example.php';

$api_key = '' . $config['api_key'];
$username = $config['username'];
$password = $config['password'];
$mobile_wallets = $config['mobile_wallets'];

try {
    // Example 2: Mobile Wallet Payment
    $walletConfig = [
        'api_key' => $api_key,
        'integration_id' => $mobile_wallets,
    ];

    $walletGateway = PaymentGatewayFactory::create('paymob', $walletConfig);

    $walletPaymentData = [
        'amount' => 100.00,
        'currency' => 'EGP',
        'payment_method' => 'wallet',
        "billing_data" => [
            "apartment" => "6",
            "first_name" => "Ammar",
            "last_name" => "Sadek",
            "street" => "938, Al-Jadeed Bldg",
            "building" => "939",
            "phone_number" => "01010101010",
            "country" => "OMN",
            "email" => "AmmarSadek@gmail.com",
            "floor" => "1",
            "state" => "Alkhuwair",
            "city" => "Alkhuwair",
        ],
    ];

    $result = $walletGateway->createPayment($walletPaymentData);
    echo '<pre>';
    print_r(json_encode($result));
    echo '</pre>';
} catch (PaymentGatewayException $e) {
    // echo 'Error: ' . $e->getMessage();
    print_r($e->toObject());
}
