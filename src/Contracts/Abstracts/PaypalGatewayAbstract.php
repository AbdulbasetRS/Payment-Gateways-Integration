<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts;

use Abdulbaset\PaymentGatewaysIntegration\Clients\PaymentGatewayClient;
use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Paypal\AuthenticationRequest;

/**
 * PaypalGatewayAbstract
 *
 * This abstract class provides a base implementation for interacting with the PayPal payment gateway.
 * It implements the `PaymentGatewayInterface` and defines essential methods for payment creation, verification, and refunds.
 * Subclasses extending this abstract class must implement the abstract methods to handle specific payment operations for the PayPal gateway.
 *
 * @link https://developer.paypal.com/docs/api/overview/ Link to the official PayPal API documentation.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Contracts
 */
abstract class PaypalGatewayAbstract implements PaymentGatewayInterface
{
    /**
     * @var PaymentGatewayClient $client
     * Instance of the PaymentGatewayClient to make API requests to the PayPal payment gateway.
     */
    protected PaymentGatewayClient $client;

    /**
     * @var array $config
     * Configuration settings for the PayPal payment gateway, such as client ID, secret, and endpoint information.
     */
    protected array $config;

    /**
     * @var string|null $accessToken
     * Access token used for authenticating API requests to PayPal.
     */
    private ?string $accessToken = null;

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
     * Initialize the PayPal gateway with the required configuration.
     * This method sets up the PayPal client and authenticates the credentials.
     *
     * @param array $config Configuration data like client ID, secret, and URLs.
     */
    public function initialize(array $config): void
    {
        $request = new AuthenticationRequest($config);
        $request->validated();

        $this->config = $config;
        $baseUrl = $this->config['mode'] === 'sandbox'
        ? 'https://api-m.sandbox.paypal.com/v1/'
        : 'https://api-m.paypal.com/v1/';

        $this->client = new PaymentGatewayClient($baseUrl);
        $this->authenticate();
    }

    /**
     * Authenticate with PayPal and retrieve the access token.
     * This token is used for subsequent API requests to PayPal.
     *
     * @throws PaymentGatewayException If authentication fails.
     */
    protected function authenticate(): void
    {
        try {
            $headers = [
                'Authorization' => 'Basic ' . base64_encode($this->config['client_id'] . ':' . $this->config['client_secret']),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $data = http_build_query(['grant_type' => 'client_credentials']);

            $response = $this->client->post('oauth2/token', $data, $headers, true);

            if (!isset($response['access_token'])) {
                throw PaymentGatewayException::authenticationError('PayPal authentication failed');
            }
            $this->accessToken = $response['access_token'];
            $this->client->setHeader('Authorization', "Bearer {$this->accessToken}");
            $this->client->setHeader('Content-Type', 'application/json');
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }

    /**
     * Retrieve details of a payment by its ID from PayPal.
     * This is used to get the status or details of an existing payment.
     *
     * @param string $paymentId The ID of the payment to retrieve.
     * @return array Payment details as returned by PayPal.
     * @throws PaymentGatewayException If retrieval fails.
     */
    protected function getPayment(string $paymentId): array
    {
        try {
            return $this->client->get('payments/payment/' . $paymentId);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }
}
