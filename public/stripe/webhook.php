<?php
require_once __DIR__ . '../../../vendor/autoload.php';

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

$config = include '../../config/stripe.example.php';

// Get the raw POST data
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {

    $stripeConfig = [
        'secret_key' => $config['secret_key'],
        'publishable_key' => $config['publishable_key'],
        'webhook_secret' => $config['webhook_secret'],
    ];

    if (empty($sig_header)) {
        throw PaymentGatewayException::validationError('No Stripe signature found');
    }

    $stripeGateway = PaymentGatewayFactory::create('stripe', $stripeConfig);

    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $webhook_secret
    );

    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            // Payment is successful and the subscription is created
            // You can provision the subscription here
            break;

        case 'payment_intent.succeeded':
            $paymentIntent = $event->data->object;
            // Payment is successful
            break;

        case 'payment_intent.payment_failed':
            $paymentIntent = $event->data->object;
            // Payment failed
            break;

        default:
            // Handle other event types
            break;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook error: ' . $e->getMessage()]);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
} catch (PaymentGatewayException $e) {
    http_response_code(400);
    echo json_encode($e->toArray());
}
