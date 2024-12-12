<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/paymob.example.php';

$api_key = '' . $config['api_key'];
$username = $config['username'];
$password = $config['password'];
$Iframe = $config['Iframe'];
$online_card = $config['online_card'];

try {
    // Example 1: Card Payment
    $cardConfig = [
        'api_key' => $api_key,
        'integration_id' => $online_card,
        'iframe_id' => $Iframe,
    ];

    $cardGateway = PaymentGatewayFactory::create('paymob', $cardConfig);

    $cardPaymentData = [
        'amount' => 100.00,
        'currency' => 'EGP',
        'payment_method' => 'card',
        "billing_data" => [
            "apartment" => "6",
            "first_name" => "Ammar",
            "last_name" => "Sadek",
            "street" => "938, Al-Jadeed Bldg",
            "building" => "939",
            "phone_number" => "01010101010",
            "country" => "EGY",
            "email" => "AmmarSadek@gmail.com",
            "floor" => "1",
            "state" => "Cairo",
            "city" => "Cairo",
            // "test" => "test one",
        ],
        // "test" => "test two",
    ];

    $result = $cardGateway->createPayment($cardPaymentData);

    echo '<pre>';
    print_r(json_encode($result));
    echo '</pre>';

} catch (PaymentGatewayException $e) {
    // echo 'Error: ' . $e->getMessage();
    print_r($e->toJson());
}
