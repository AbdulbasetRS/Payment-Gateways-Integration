<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Unit;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Gateways\PaypalGateway;
use PHPUnit\Framework\TestCase;

class PaypalGatewayTest extends TestCase
{
    private array $config;
    private $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $configFile = __DIR__ . '/../../config/paypal.example.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found. Please create config/paypal.php');
        }

        $config = include $configFile;
        $this->config = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'mode' => 'sandbox',
            'success_url' => $config['success_url'],
            'cancel_url' => $config['cancel_url'],
        ];

        // $this->gateway = PaymentGatewayFactory::create('paypal', $this->config);
        $this->gateway = $this->createMock(PaymentGatewayInterface::class);

    }

    public function testInitialization()
    {
        $gateway = new PaypalGateway();
        $gateway->initialize($this->config);

        $this->assertInstanceOf(
            PaymentGatewayInterface::class,
            $gateway
        );
    }

    public function testMissingConfig()
    {
        $this->expectException(PaymentGatewayException::class);

        $gateway = new PaypalGateway();
        $gateway->initialize(['client_id' => 'test']);
    }

    public function testCreatePayment()
    {
        $paymentData = [
            'amount' => 100.0,
            'currency' => 'USD',
            'description' => 'Test Payment',
        ];

        $this->gateway->expects($this->once())
            ->method('createPayment')
            ->with($this->equalTo($paymentData))
            ->willReturn(['payment_id' => 'test_payment_id', 'status' => 'success']);

        $gateway = new PaypalGateway();
        $gateway->initialize($this->config);
        $result = $this->gateway->createPayment($paymentData);

        $this->assertIsArray($result);
        $this->assertEquals('test_payment_id', $result['payment_id']);
        $this->assertEquals('success', $result['status']);
    }

    public function testVerifyPayment()
    {
        $paymentId = 'test_payment_id';

        $this->gateway->expects($this->once())
            ->method('verifyPayment')
            ->with($this->equalTo($paymentId))
            ->willReturn(['payment_id' => $paymentId, 'status' => 'verified']);

        $gateway = new PaypalGateway();
        $gateway->initialize($this->config);
        $result = $this->gateway->verifyPayment($paymentId);

        $this->assertIsArray($result);
        $this->assertEquals('test_payment_id', $result['payment_id']);
        $this->assertEquals('verified', $result['status']);
    }

    public function testRefund()
    {
        $paymentId = 'test_payment_id';
        $amount = 50.0;

        $this->gateway->expects($this->once())
            ->method('refund')
            ->with($this->equalTo($paymentId), $this->equalTo($amount))
            ->willReturn(['payment_id' => $paymentId, 'status' => 'refunded']);

        $gateway = new PaypalGateway();
        $gateway->initialize($this->config);
        $result = $this->gateway->refund($paymentId, $amount);

        $this->assertIsArray($result);
        $this->assertEquals('test_payment_id', $result['payment_id']);
        $this->assertEquals('refunded', $result['status']);
    }
}
