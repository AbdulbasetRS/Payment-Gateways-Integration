<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Tests\Unit\Clients;

use Abdulbaset\PaymentGatewaysIntegration\Clients\PaymentGatewayClient;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use PHPUnit\Framework\TestCase;

class PaymentGatewayClientTest extends TestCase
{
    private PaymentGatewayClient $client;

    protected function setUp(): void
    {
        $this->client = new PaymentGatewayClient('https://api.example.com');
    }

    public function testClientInitialization()
    {
        $this->assertInstanceOf(PaymentGatewayClient::class, $this->client);
    }

    public function testSetHeader()
    {
        $this->client->setHeader('Authorization', 'Bearer test_token');
        $this->expectNotToPerformAssertions();
    }

    public function testSetBaseUrl()
    {
        $this->client->setBaseUrl('https://new-api.example.com');
        $this->expectNotToPerformAssertions();
    }

    public function testPostRequest()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->client->post('/test', ['data' => 'test']);
    }

    public function testGetRequest()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->client->get('/test', ['query' => 'test']);
    }

    public function testPutRequest()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->client->put('/test', ['data' => 'test']);
    }

    public function testDeleteRequest()
    {
        $this->expectException(PaymentGatewayException::class);
        $this->client->delete('/test');
    }
}
