<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Unit;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;
use PHPUnit\Framework\TestCase;

class StripeGatewayTest extends TestCase
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
            'webhook_secret' => $config['webhook_secret'],
            'success_url' => $config['success_url'],
            'cancel_url' => $config['cancel_url'],
        ];

        $this->gateway = PaymentGatewayFactory::create('stripe', $this->config);

        $this->mockGateway = $this->createMock(PaymentGatewayInterface::class);
    }

    public function testInitialization()
    {
        $this->assertInstanceOf(
            \Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface::class,
            $this->gateway
        );
    }

    public function testMissingConfig()
    {
        $this->expectException(PaymentGatewayException::class);
        PaymentGatewayFactory::create('stripe', ['secret_key' => 'test']);
    }

    public function testCreateCardPayment()
    {
        $paymentData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_method' => 'card',
            'customerInfo' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1234567890',
                'description' => 'Test payment',
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
        $this->assertArrayHasKey('amount', $result['data']);
        $this->assertArrayHasKey('currency', $result['data']);
        $this->assertArrayHasKey('payment_status', $result['data']);
    }

    public function testVerifyPayment()
    {
        $paymentId = 'pi_test_payment';
        $amount = 50.00;

        $this->mockGateway->method('verifyPayment')
            ->with($paymentId)
            ->willReturn([
                'status' => 200,
                'message' => 'Refund processed successfully',
                'data' => [
                    'transaction_id' => 'transaction_id',
                    'amount' => $amount,
                    'currency' => 'USD',
                    'payment_status' => 'completed',
                    'payment_method' => 'payment_method',
                    'paid' => true,
                ],
            ]);

        $result = $this->mockGateway->verifyPayment($paymentId);

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('transaction_id', $result['data']);
        $this->assertArrayHasKey('amount', $result['data']);
        $this->assertArrayHasKey('currency', $result['data']);
        $this->assertArrayHasKey('payment_status', $result['data']);
        $this->assertArrayHasKey('payment_method', $result['data']);
        $this->assertArrayHasKey('paid', $result['data']);
    }

    public function testRefund()
    {
        $paymentId = 'pi_test_payment';
        $amount = 50.00;

        $this->mockGateway->method('refund')
            ->with($this->equalTo($paymentId), $this->equalTo($amount))
            ->willReturn([
                'status' => 200,
                'message' => 'Refund processed successfully',
                'data' => [
                    'transaction_id' => 'transaction_id',
                    'amount' => $amount,
                    'currency' => 'USD',
                    'payment_status' => 'completed',
                    'refund_id' => 'refund_id',
                    'paid' => true,
                ],
            ]);

        $result = $this->mockGateway->refund($paymentId, $amount);

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('refund_id', $result['data']);
        $this->assertArrayHasKey('transaction_id', $result['data']);
        $this->assertArrayHasKey('amount', $result['data']);
        $this->assertArrayHasKey('currency', $result['data']);
        $this->assertArrayHasKey('payment_status', $result['data']);
    }
}
