<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Unit;

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;
use PHPUnit\Framework\TestCase;

class PaymobGatewayTest extends TestCase
{
    private array $config;
    private $gateway;

    protected function setUp(): void
    {
        $configFile = __DIR__ . '/../../config/paymob.example.php';
        if (!file_exists($configFile)) {
            throw new \RuntimeException('Config file not found. Please create config/paymob.example.php');
        }

        $config = include $configFile;

        $this->config = [
            'api_key' => $config['api_key'],
            'integration_id' => $config['online_card'],
            'iframe_id' => $config['Iframe'],
        ];

        $this->gateway = PaymentGatewayFactory::create('paymob', $this->config);
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
        PaymentGatewayFactory::create('paymob', ['api_key' => 'test']);
    }

    public function testCreateCardPayment()
    {
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

        $result = $this->gateway->createPayment($paymentData);

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('payment_key', $result['data']);
        $this->assertArrayHasKey('payment_url', $result['data']);
        $this->assertArrayHasKey('order_id', $result['data']);
        $this->assertArrayHasKey('transaction_id', $result['data']);
    }
}
