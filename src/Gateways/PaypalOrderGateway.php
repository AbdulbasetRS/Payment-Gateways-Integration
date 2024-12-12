<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Gateways;

use Abdulbaset\PaymentGatewaysIntegration\Clients\PaymentGatewayClient;
use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Paypal\AuthenticationRequest;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Paypal\PaymentRequest;
use Abdulbaset\PaymentGatewaysIntegration\Responses\PaymentGatewayResponse;
use Abdulbaset\PaymentGatewaysIntegration\Utils\Collection;

/**
 * PaypalOrderGateway Class
 *
 * This class provides integration with the PayPal payment gateway. It implements
 * the PaymentGatewayInterface and offers methods for creating payments,
 * verifying payments, and processing refunds.
 *
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Gateways
 */
class PaypalOrderGateway implements PaymentGatewayInterface
{
    /**
     * @var PaymentGatewayClient $client The client used to communicate with the PayPal API.
     */
    private PaymentGatewayClient $client;

    /**
     * @var array $config Configuration settings for the gateway.
     */
    private array $config;

    /**
     * @var string|null $accessToken The access token for authenticated API requests.
     */
    private ?string $accessToken = null;

    /**
     * Initializes the PayPal payment gateway with the provided configuration.
     *
     * @param array $config Configuration parameters including client ID, client secret, and mode.
     * @throws PaymentGatewayException If authentication fails.
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
     * Authenticates with the PayPal API using client credentials and retrieves an access token.
     *
     * @throws PaymentGatewayException If authentication fails.
     */
    private function authenticate(): void
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
     * Creates a payment order with the specified payment data.
     *
     * @param array $paymentData The payment data, including amount, currency, description, and redirect URLs.
     * @return array A response containing the payment URL and related details.
     * @throws PaymentGatewayException If payment creation fails.
     */
    public function createPayment(array $paymentData): array
    {
        $request = new PaymentRequest($paymentData);
        $validatedData = $request->validated();

        try {
            $response = $this->client->post('checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => 'store_mobile_world_order_1234',
                    'amount' => [
                        'currency_code' => $validatedData['currency'],
                        'value' => number_format($validatedData['amount'], 2, '.', ''),
                    ],
                    'description' => $validatedData['description'] ?? 'Payment transaction',
                ]],
                'redirect_urls' => [
                    'return_url' => $this->config['success_url'],
                    'cancel_url' => $this->config['cancel_url'],
                ],
            ]);

            if (!isset($response['id'])) {
                throw PaymentGatewayException::paymentError('Failed to create PayPal order');
            }

            $links = new Collection($response['links'] ?? []);
            $approveLink = $links->firstWhere('rel', 'approve');
            $approveUrl = $approveLink['href'] ?? null;

            if (!$approveUrl) {
                throw PaymentGatewayException::paymentError('PayPal approval URL not found');
            }

            return PaymentGatewayResponse::success('Payment URL generated successfully', [
                'payment_key' => $response['id'],
                'payment_url' => $approveUrl,
                'order_id' => $response['id'],
                'transaction_id' => $response['id'],
                'status' => $response['status'],
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }

    /**
     * Verifies the status of a PayPal payment transaction.
     *
     * @param string $paymentId The ID of the PayPal payment transaction.
     * @return array The verification details including transaction ID, order ID, amount, currency, status, and payment status.
     * @throws PaymentGatewayException If payment verification fails.
     */
    public function verifyPayment(string $paymentId): array
    {
        try {
            $response = $this->client->get("checkout/orders/{$paymentId}");

            return PaymentGatewayResponse::success('Payment verified successfully', [
                'transaction_id' => $response['id'],
                'order_id' => $response['id'],
                'amount' => $response['purchase_units'][0]['amount']['value'] ?? 0,
                'currency' => $response['purchase_units'][0]['amount']['currency_code'] ?? '',
                'payment_status' => $response['status'],
                'paid' => $response['status'] === 'COMPLETED',
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }

    /**
     * Processes a refund for a specified PayPal payment transaction.
     *
     * @param string $paymentId The ID of the PayPal payment transaction to refund.
     * @param float $amount The amount to refund.
     * @return array The refund details including refund ID, transaction ID, amount, currency, and status.
     * @throws PaymentGatewayException If the refund process fails.
     */
    public function refund(string $paymentId, float $amount): array
    {
        try {
            // First, get the capture ID from the order
            $order = $this->client->get("checkout/orders/{$paymentId}");
            $captureId = $order['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

            if (!$captureId) {
                throw PaymentGatewayException::paymentError('Capture ID not found for this payment');
            }

            // Process the refund
            $response = $this->client->post("payments/captures/{$captureId}/refund", [
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => $order['purchase_units'][0]['amount']['currency_code'],
                ],
            ]);

            return PaymentGatewayResponse::success('Refund processed successfully', [
                'refund_id' => $response['id'],
                'transaction_id' => $paymentId,
                'amount' => $response['amount']['value'],
                'currency' => $response['amount']['currency_code'],
                'status' => $response['status'],
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }
}
