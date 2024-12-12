<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Feature;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;
use PHPUnit\Framework\TestCase;

class PaymobPaymentTest extends TestCase
{
    private array $config;
    private $gateway;
    private $mockGateway;

    protected function setUp(): void
    {
        $configFile = __DIR__ . '/../../config/paymob.example.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found. Please create config/paymob.php');
        }

        $config = include $configFile;

        // Card payment gateway setup
        $this->config = [
            'api_key' => $config['api_key'],
            'integration_id' => $config['online_card'],
            'iframe_id' => $config['Iframe'],
        ];

        $this->gateway = PaymentGatewayFactory::create('paymob', $this->config);

        $this->mockGateway = $this->createMock(PaymentGatewayInterface::class);
    }

    public function testCompleteCardPaymentFlow()
    {
        // 1. Create payment
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'EGP',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone_number' => '01010101010',
                'apartment' => '123',
                'building' => '123',
                'street' => 'Test St',
                'floor' => '1',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'EG',
            ],
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
        $this->mockGateway->method('verifyPayment')
        ->with($this->equalTo($result['data']['order_id']))
        ->willReturn([
            'status' => 200,
            'message' => 'Payment verified successfully',
            'data' => [
                'payment_status' => 'paid',
                'paid' => true,
            ],
        ]);
        $verificationResult = $this->mockGateway->verifyPayment($result['data']['order_id']);
        $this->assertEquals(200, $verificationResult['status']);
        $this->assertNotEmpty($verificationResult['message']);
        $this->assertArrayHasKey('data', $verificationResult);
        $this->assertArrayHasKey('payment_status', $verificationResult['data']);

        // // 3. Refund payment
        $this->mockGateway->method('refund')
            ->with($this->equalTo($result['data']['order_id']), $this->equalTo(100.00))
            ->willReturn([
                'status' => 200,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund_id' => 'test_refund_id',
                ],
            ]);
        if ($verificationResult['data']['paid']) {
            $refundResult = $this->mockGateway->refund($result['data']['order_id'], 100.00);
            $this->assertEquals(200, $refundResult['status']);
            $this->assertNotEmpty($refundResult['message']);
            $this->assertArrayHasKey('data', $refundResult);
            $this->assertArrayHasKey('refund_id', $refundResult['data']);
        }
    }

    public function testCompleteWalletPaymentFlow()
    {
        // Setup wallet gateway
        $config = include __DIR__ . '/../../config/paymob.example.php';
        $walletConfig = [
            'api_key' => $config['api_key'],
            'integration_id' => $config['mobile_wallets'],
        ];

        $walletGateway = PaymentGatewayFactory::create('paymob', $walletConfig);

        // Create wallet payment
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'EGP',
            'payment_method' => 'wallet',
            'billing_data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone_number' => '01010101010',
                'apartment' => '123',
                'building' => '123',
                'street' => 'Test St',
                'floor' => '1',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'EG',
            ],
        ];

        $result = $walletGateway->createPayment($paymentData);

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('payment_key', $result['data']);
        $this->assertArrayHasKey('payment_url', $result['data']);
        $this->assertArrayHasKey('order_id', $result['data']);
        $this->assertArrayHasKey('transaction_id', $result['data']);

        // Verify payment
        $verificationResult = $walletGateway->verifyPayment($result['data']['transaction_id']);
        $this->assertEquals(200, $verificationResult['status']);
        $this->assertNotEmpty($verificationResult['message']);
        $this->assertArrayHasKey('data', $verificationResult);
    }

    public function testInvalidApiKey()
    {
        $this->expectException(PaymentGatewayException::class);

        $invalidConfig = [
            'api_key' => 'invalid_key',
            'integration_id' => 'test_integration_id',
            'iframe_id' => 'test_iframe_id',
        ];

        $gateway = PaymentGatewayFactory::create('paymob', $invalidConfig);

        $paymentData = [
            'amount' => 100.00,
            'currency' => 'EGP',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone_number' => '01000000000',
                'apartment' => '123',
                'building' => '123',
                'street' => 'Test St',
                'floor' => '1',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'EG',
            ],
        ];

        $gateway->createPayment($paymentData);
    }

    public function testInvalidAmount()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => -100.00, // Invalid negative amount
            'currency' => 'EGP',
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone_number' => '01000000000',
                'apartment' => '123',
                'building' => '123',
                'street' => 'Test St',
                'floor' => '1',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'EG',
            ],
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidCurrency()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => 100.00,
            'currency' => 'INVALID', // Invalid currency code
            'payment_method' => 'card',
            'billing_data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone_number' => '01000000000',
                'apartment' => '123',
                'building' => '123',
                'street' => 'Test St',
                'floor' => '1',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'EG',
            ],
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testMissingBillingData()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => 100.00,
            'currency' => 'EGP',
            'payment_method' => 'card',
            // Missing billing_data
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidPhoneNumber()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => 100.00,
            'currency' => 'EGP',
            'payment_method' => 'wallet',
            'billing_data' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone_number' => 'invalid_phone', // Invalid phone number format
                'apartment' => '123',
                'building' => '123',
                'street' => 'Test St',
                'floor' => '1',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'country' => 'EG',
            ],
        ];

        $this->gateway->createPayment($paymentData);
    }
}
