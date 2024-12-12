<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Stripe\AuthenticationRequest;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * StripeGatewayAbstract
 *
 * This abstract class provides a base implementation for interacting with the Stripe payment gateway.
 * It implements the `PaymentGatewayInterface` and defines essential methods for payment creation, verification, and refunds.
 * Subclasses extending this abstract class must implement the abstract methods to handle specific payment operations for Stripe.
 *
 * @link https://docs.stripe.com/ Link to the official Stripe API documentation.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Contracts
 */
abstract class StripeGatewayAbstract implements PaymentGatewayInterface
{
    /**
     * @var array Configuration settings for the Stripe gateway.
     */
    protected array $config;

    /**
     * Creates a payment request with the specified payment details.
     *
     * @param array $paymentData Payment details, including amount, currency, and customer information.
     * @return array Response containing payment details and status.
     */
    abstract public function createPayment(array $paymentData): array;

    /**
     * Verifies the status of a payment using its unique ID.
     *
     * @param string $paymentId Unique identifier of the payment to be verified.
     * @return array Response containing payment verification details and status.
     */
    abstract public function verifyPayment(string $paymentId): array;

    /**
     * Processes a refund for a specified payment.
     *
     * @param string $paymentId Unique identifier of the payment to be refunded.
     * @param float $amount Amount to be refunded.
     * @return array Response containing refund details and status.
     */
    abstract public function refund(string $paymentId, float $amount): array;

    /**
     * Initializes the Stripe payment gateway with the given configuration.
     * Sets up the API key and other required settings.
     *
     * @param array $config Configuration settings including API keys and URLs.
     * @throws PaymentGatewayException If validation of configuration fails.
     */
    public function initialize(array $config): void
    {
        $request = new AuthenticationRequest($config);
        $validated = $request->validated();
        $this->config = $config;

        // Initialize Stripe
        Stripe::setApiKey($validated['secret_key']);
    }

    /**
     * Formats metadata for Stripe API, converting nested arrays to JSON strings.
     *
     * @param array $data Metadata to format.
     * @return array Formatted metadata array.
     */
    protected function formatMetadata(array $data): array
    {
        $metadata = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $metadata[$key] = json_encode($value);
            } else {
                $metadata[$key] = (string) $value;
            }
        }
        return $metadata;
    }

    /**
     * Creates a Stripe Checkout session for processing payments.
     *
     * @param array $paymentData Payment details including amount, currency, and customer info.
     * @return Session Stripe Checkout Session object.
     * @throws PaymentGatewayException If Stripe API returns an error.
     */
    protected function createCheckoutSession(array $paymentData): Session
    {
        try {
            return Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $paymentData['currency'],
                        'unit_amount' => (int) ($paymentData['amount'] * 100),
                        'product_data' => [
                            'name' => $paymentData['customerInfo']['description'] ?? 'Payment',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->config['success_url'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->config['cancel_url'],
                'customer_email' => $paymentData['customerInfo']['email'] ?? null,
                'metadata' => isset($paymentData['customerInfo']) ?
                $this->formatMetadata($paymentData['customerInfo']) : [],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }
}
