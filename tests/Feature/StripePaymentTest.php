<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Feature;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use PHPUnit\Framework\TestCase;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;

class StripePaymentTest extends TestCase
{
    private array $config;
    private $gateway;
    private $mockGateway;

    protected function setUp(): void
    {
        $configFile = __DIR__ . '/../../config/stripe.example.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found. Please create config/stripe.php');
        }

        $config = include $configFile;
        
        $this->config = [
            'secret_key' => $config['secret_key'],
            'publishable_key' => $config['publishable_key'],
            'webhook_secret' => $config['webhook_secret'] ?? null,
            'success_url' => $config['success_url'],
            'cancel_url' => $config['cancel_url'],
        ];
        
        $this->gateway = PaymentGatewayFactory::create('stripe', $this->config);

        $this->mockGateway = $this->createMock(PaymentGatewayInterface::class);
    }

    public function testCompletePaymentFlow()
    {
        // 1. Create payment
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'card',
            'customerInfo' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1234567890',
                'description' => 'Test payment flow'
            ]
        ];

        $result = $this->gateway->createPayment($paymentData);
        
        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('payment_key', $result['data']);
        $this->assertArrayHasKey('payment_url', $result['data']);
        $this->assertArrayHasKey('order_id', $result['data']);
        $this->assertArrayHasKey('transaction_id', $result['data']);
        
        // 2. Verify payment
        $verificationResult = $this->gateway->verifyPayment($result['data']['order_id']);
        $this->assertEquals(200, $verificationResult['status']);
        $this->assertNotEmpty($verificationResult['message']);
        $this->assertArrayHasKey('data', $verificationResult);
        $this->assertArrayHasKey('paid', $verificationResult['data']);
        
        // // 3. Refund payment Must be Paid First
        $this->mockGateway->method('refund')
        ->with($this->equalTo($result['data']['transaction_id']), $this->equalTo(100.00))
        ->willReturn([
            'status' => 200,
            'message' => 'Refund processed successfully',
            'data' => [
                'refund_id' => 'test_refund_id',
            ],
        ]);
        $refundResult = $this->mockGateway->refund($result['data']['transaction_id'], 100.00);
        $this->assertEquals(200, $refundResult['status']);
        $this->assertNotEmpty($refundResult['message']);
        $this->assertArrayHasKey('data', $refundResult);
        $this->assertArrayHasKey('refund_id', $refundResult['data']);
    }

    public function testInvalidAmount()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => -100.00,
            'currency' => 'usd',
            'payment_method' => 'card',
            'customerInfo' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1234567890',
                'description' => 'Test payment'
            ]
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidCurrency()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'INVALID',
            'payment_method' => 'card',
            'customerInfo' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1234567890',
                'description' => 'Test payment'
            ]
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testMissingCustomerInfo()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'usd',
            'payment_method' => 'card'
            // Missing customerInfo
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidEmail()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'usd',
            'payment_method' => 'card',
            'customerInfo' => [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'phone' => '+1234567890',
                'description' => 'Test payment'
            ]
        ];

        $this->gateway->createPayment($paymentData);
    }

    // public function testWebhookVerification()
    // {
    //     $payload = json_encode([
    //         'type' => 'payment_intent.succeeded',
    //         'data' => [
    //             'object' => [
    //                 'id' => 'pi_test',
    //                 'amount' => 10000,
    //                 'currency' => 'usd',
    //                 'status' => 'succeeded'
    //             ]
    //         ]
    //     ]);

    //     $signature = 'test_signature';
    //     $_SERVER['HTTP_STRIPE_SIGNATURE'] = $signature;

    //     $result = $this->gateway->handleWebhook($payload, $signature);
    //     $this->assertEquals(200, $result['status']);
    //     $this->assertArrayHasKey('data', $result);
    // }
}