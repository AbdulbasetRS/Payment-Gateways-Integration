<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Feature;

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;
use PHPUnit\Framework\TestCase;

class TapPaymentTest extends TestCase
{
    private array $config;
    private $gateway;

    protected function setUp(): void
    {
        $configFile = __DIR__ . '/../../config/tap.example.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found. Please create config/tap.php');
        }

        $config = include $configFile;
        
        $this->config = [
            'secret_key' => $config['secret_key'],
            'publishable_key' => $config['publishable_key'],
            'success_url' => $config['success_url'],
            'cancel_url' => $config['cancel_url'],
        ];
        
        $this->gateway = PaymentGatewayFactory::create('tap', $this->config);
    }

    public function testCompletePaymentFlow()
    {
        // 1. Create payment
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone_number' => '12345678901'
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
        $verificationResult = $this->gateway->verifyPayment($result['data']['transaction_id']);
        $this->assertEquals(200, $verificationResult['status']);
        $this->assertNotEmpty($verificationResult['message']);
        $this->assertArrayHasKey('data', $verificationResult);
        
        // 3. Refund payment (only if payment was successful)
        if ($verificationResult['data']['paid']) {
            $refundResult = $this->gateway->refund($result['data']['transaction_id'], 100.00);
            $this->assertEquals(200, $refundResult['status']);
            $this->assertNotEmpty($refundResult['message']);
            $this->assertArrayHasKey('data', $refundResult);
        }
    }

    public function testInvalidAmount()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => -100.00,
            'currency' => 'USD',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone_number' => '12345678901'
            ]
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidEmail()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'invalid-email',
                'phone_number' => '12345678901'
            ]
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidPhoneNumber()
    {
        $this->expectException(PaymentGatewayException::class);
        
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone_number' => '123'
            ]
        ];

        $this->gateway->createPayment($paymentData);
    }
}