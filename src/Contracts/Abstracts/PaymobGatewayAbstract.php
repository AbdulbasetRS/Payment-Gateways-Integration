<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts;

use Abdulbaset\PaymentGatewaysIntegration\Clients\PaymentGatewayClient;
use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;

/**
 * PaymobGatewayAbstract
 *
 * This abstract class provides a base implementation for interacting with the Paymob payment gateway.
 * It implements the `PaymentGatewayInterface` and defines essential methods for payment creation, verification, and refunds.
 * Subclasses extending this abstract class must implement the abstract methods to handle specific payment operations for the Paymob gateway.
 *
 * @link https://developers.paymob.com/egypt/getting-started-egypt Link to the official Paymob API documentation.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Contracts
 */
abstract class PaymobGatewayAbstract implements PaymentGatewayInterface
{
    /**
     * @var PaymentGatewayClient $client
     * Instance of the PaymentGatewayClient to make API requests to the Paymob payment gateway.
     */
    protected PaymentGatewayClient $client;

    /**
     * @var array $config
     * Configuration settings for the Paymob payment gateway, such as API keys and endpoint information.
     */
    protected array $config;

    /**
     * @var string|null $authToken
     * Authentication token used for making API requests to Paymob.
     */
    protected ?string $authToken = null;

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
     * Initialize the Paymob gateway with the required configuration.
     * This method sets up the Paymob client and authenticates the credentials.
     *
     * @param array $config Configuration data such as API keys and other credentials.
     */
    public function initialize(array $config): void
    {
        $this->config = $config;
        $this->client = new PaymentGatewayClient('https://accept.paymob.com/api/');
        $this->authenticate();
    }

    /**
     * Authenticate with Paymob and retrieve the authentication token.
     * This token is used for making subsequent API requests to Paymob.
     *
     * @throws PaymentGatewayException If authentication fails.
     */
    protected function authenticate(): void
    {
        try {
            $response = $this->client->post('auth/tokens', [
                'api_key' => $this->config['api_key'],
            ]);

            if (!isset($response['token'])) {
                throw PaymentGatewayException::authenticationError('Authentication failed');
            }

            $this->authToken = $response['token'];
            $this->client->setHeader('Authorization', "Bearer {$this->authToken}");
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }
    
    /**
     * Create a wallet payment request for a given token and phone number.
     * This is used for payments using wallet services.
     *
     * @param string $token Payment token generated during payment creation.
     * @param string $phoneNumber Phone number of the user making the payment.
     * @return array Response containing the payment URL and other details.
     *
     * @throws PaymentGatewayException
     */
    protected function createWalletPayment(string $token, string $phoneNumber): array
    {
        try {
            $response = $this->client->post('acceptance/payments/pay', [
                'payment_token' => $token,
                'source' => [
                    'identifier' => $phoneNumber,
                    'subtype' => 'WALLET',
                ],
            ]);

            if (!isset($response['redirect_url'])) {
                throw PaymentGatewayException::paymentError('Failed to create wallet payment URL');
            }

            return [
                'payment_key' => $token,
                'payment_url' => $response['redirect_url'] ?? $response['iframe_redirection_url'] ?? null,
                'order_id' => $response['order']['id'] ?? null,
                'transaction_id' => $response['id'] ?? null,
            ];
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Retrieve the payment URL based on the payment method and token.
     * This is used to generate a URL for card or wallet payments.
     *
     * @param string $paymentMethod Type of payment method (e.g., 'card').
     * @param string $token Payment token generated during payment creation.
     * @return string URL for the payment method (e.g., for card payments, returns an iframe URL).
     *
     * @throws PaymentGatewayException If the payment method is unsupported.
     */
    protected function getPaymentUrl(string $paymentMethod, string $token): string
    {
        if ($paymentMethod === 'card') {
            $iframeId = $this->config['iframe_id'] ?? '';
            return "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$token}";
        }

        throw PaymentGatewayException::paymentError("Unsupported payment method: {$paymentMethod}");
    }
}
