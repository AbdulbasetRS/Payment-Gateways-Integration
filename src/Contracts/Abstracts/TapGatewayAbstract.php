<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts;

use Abdulbaset\PaymentGatewaysIntegration\Clients\PaymentGatewayClient;
use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Tap\AuthenticationRequest;

/**
 * TapGatewayAbstract
 *
 * This abstract class provides a base implementation for interacting with the Tap payment gateway.
 * It implements the `PaymentGatewayInterface` and defines essential methods for payment creation, verification, and refunds.
 * Subclasses extending this abstract class must implement the abstract methods to handle specific payment operations for the Tap gateway.
 *
 * @link https://developers.tap.company/reference/api-endpoint Link to the official Tap API documentation.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Contracts
 */
abstract class TapGatewayAbstract implements PaymentGatewayInterface
{
    /**
     * @var PaymentGatewayClient $client
     * Instance of the PaymentGatewayClient to make API requests to the Tap payment gateway.
     */
    protected PaymentGatewayClient $client;

    /**
     * @var array $config
     * Configuration settings for the Tap payment gateway, such as API keys and endpoint information.
     */
    protected array $config;

    /**
     * Create a payment request with the specified data.
     *
     * @param array $paymentData Payment details such as amount, currency, and payer information.
     * @return array Response containing payment details and status.
     *
     * @throws PaymentGatewayException
     */
    abstract public function createPayment(array $paymentData): array;

    /**
     * Verify the status of a payment using the unique payment ID.
     *
     * @param string $paymentId Unique identifier of the payment to be verified.
     * @return array Response containing payment verification details and status.
     *
     * @throws PaymentGatewayException
     */
    abstract public function verifyPayment(string $paymentId): array;

    /**
     * Process a refund for a given payment.
     *
     * @param string $paymentId Unique identifier of the payment to be refunded.
     * @param float $amount Amount to be refunded.
     * @return array Response containing refund details and status.
     *
     * @throws PaymentGatewayException
     */
    abstract public function refund(string $paymentId, float $amount): array;

    /**
     * Initializes the payment gateway with the necessary configuration details.
     * This method sets up the payment gateway client with authentication and other necessary headers.
     *
     * @param array $config Configuration details, including API keys, endpoint URLs, etc.
     * @return void
     *
     * @throws PaymentGatewayException
     */
    public function initialize(array $config): void
    {
        $request = new AuthenticationRequest($config);
        $request->validated();

        $this->config = $config;
        $this->client = new PaymentGatewayClient('https://api.tap.company/v2/');
        $this->client->setHeader('Authorization', "Bearer {$this->config['secret_key']}");
        $this->client->setHeader('accept', "application/json");
        $this->client->setHeader('content', "application/json");
    }
}
