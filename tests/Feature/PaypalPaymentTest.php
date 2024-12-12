<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Feature;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;
use PHPUnit\Framework\TestCase;

class PaypalPaymentTest extends TestCase
{
    private array $config;
    private $gateway;
    private $mockGateway;

    protected function setUp(): void
    {
        $configFile = __DIR__ . '/../../config/paypal.example.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found. Please create config/paypal.php');
        }

        $config = include $configFile;

        $this->config = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'mode' => $config['mode'],
            'success_url' => $config['success_url'],
            'cancel_url' => $config['cancel_url'],
        ];

        $this->gateway = PaymentGatewayFactory::create('paypal', $this->config);

        $this->mockGateway = $this->createMock(PaymentGatewayInterface::class);

    }

    public function testCompletePaymentFlow()
    {
        // 1. Create payment
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'PAYPAL',
            'description' => 'Test payment flow',
        ];
        $result = $this->gateway->createPayment($paymentData);

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('payment_url', $result['data']);
        $this->assertArrayHasKey('payment_id', $result['data']);

        // 2. Verify payment

        $this->mockGateway->method('verifyPayment')
            ->with($this->equalTo($result['data']['payment_id']))
            ->willReturn([
                'status' => 200,
                'message' => 'Payment verified successfully',
                'data' => [
                    'payment_status' => 'paid',
                    'paid' => true,
                ],
            ]);
        $verificationResult = $this->mockGateway->verifyPayment($result['data']['payment_id']);
        $this->assertEquals(200, $verificationResult['status']);
        $this->assertNotEmpty($verificationResult['message']);
        $this->assertArrayHasKey('data', $verificationResult);
        $this->assertArrayHasKey('payment_status', $verificationResult['data']);

        // 3. Refund payment (only if payment was successful)
        $this->mockGateway->method('refund')
            ->with($this->equalTo($result['data']['payment_id']), $this->equalTo(100.00))
            ->willReturn([
                'status' => 200,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund_id' => 'test_refund_id',
                ],
            ]);
        if ($verificationResult['data']['paid']) {
            $refundResult = $this->mockGateway->refund($result['data']['payment_id'], 100.00);
            $this->assertEquals(200, $refundResult['status']);
            $this->assertNotEmpty($refundResult['message']);
            $this->assertArrayHasKey('data', $refundResult);
            $this->assertArrayHasKey('refund_id', $refundResult['data']);
        }
    }

    public function testInvalidAmount()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => -100.00,
            'currency' => 'USD',
            'payment_method' => 'PAYPAL',
            'description' => 'Test payment',
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidCurrency()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => 100.00,
            'currency' => 'INVALID',
            'payment_method' => 'PAYPAL',
            'description' => 'Test payment',
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testMissingRequiredFields()
    {
        $this->expectException(PaymentGatewayException::class);

        $paymentData = [
            'amount' => 100.00,
            // Missing currency
            'payment_method' => 'PAYPAL',
        ];

        $this->gateway->createPayment($paymentData);
    }

    public function testInvalidPaymentId()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->gateway->verifyPayment('invalid_payment_id');
    }

    public function testInvalidRefund()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->gateway->refund('invalid_payment_id', 100.00);
    }

    public function testRefundWithInvalidAmount()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->gateway->refund('valid_payment_id', -100.00);
    }
}
