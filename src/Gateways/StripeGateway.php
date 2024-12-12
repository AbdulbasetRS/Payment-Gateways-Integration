<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Gateways;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts\StripeGatewayAbstract;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Stripe\PaymentRequest;
use Abdulbaset\PaymentGatewaysIntegration\Responses\PaymentGatewayResponse;
use Stripe\PaymentIntent;

/**
 * StripeGateway Class
 *
 * Handles payment operations using Stripe's API.
 * Implements the PaymentGatewayInterface to provide a unified payment gateway structure.
 *
 * @see https://github.com/AbdulbasetRS/Payment-Gateways-Integration/blob/main/docs/stripe.md Additional documentation and source code on GitHub.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Gateways
 */
class StripeGateway extends StripeGatewayAbstract
{
    /**
     * Creates a payment intent and a checkout session.
     *
     * @param array $paymentData Payment details including amount, currency, and customer info.
     * @return array Payment response data including URLs and transaction IDs.
     * @throws PaymentGatewayException If payment creation fails.
     */
    public function createPayment(array $paymentData): array
    {

        $validatedData = new PaymentRequest($paymentData);
        $validated = $validatedData->validated();

        try {
            // Create a Checkout Session
            $session = $this->createCheckoutSession($validated);

            // Create a Payment Intent
            $intent = PaymentIntent::create([
                'amount' => (int) ($validated['amount'] * 100),
                'currency' => $validated['currency'],
                'payment_method_types' => ['card'],
                'description' => $validated['customerInfo']['description'] ?? null,
                'metadata' => isset($validated['customerInfo']) ?
                $this->formatMetadata($validated['customerInfo']) : [],
            ]);

            return PaymentGatewayResponse::success('Payment intent created successfully', [
                'payment_key' => $intent->client_secret,
                'payment_url' => $session->url,
                'order_id' => $session->id,
                'transaction_id' => $intent->id,
                'amount' => $validated['amount'],
                'currency' => $intent->currency,
                'payment_status' => $session->payment_status,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Verifies the status of a payment using its session ID.
     *
     * @param string $paymentId Stripe Checkout Session ID.
     * @return array Payment verification details including status and transaction data.
     * @throws PaymentGatewayException If verification fails.
     */
    public function verifyPayment(string $paymentId): array
    {
        try {
            // Retrieve the Checkout Session
            $session = \Stripe\Checkout\Session::retrieve($paymentId);

            if (!$session) {
                throw PaymentGatewayException::paymentError('Payment session not found');
            }

            // Retrieve the associated Payment Intent
            $intent = $session->payment_intent ? \Stripe\PaymentIntent::retrieve($session->payment_intent) : null;

            return PaymentGatewayResponse::success('Payment verified successfully', [
                'transaction_id' => $session->payment_intent,
                'order_id' => $session->id,
                'amount' => $session->amount_total / 100,
                'currency' => $session->currency,
                'payment_status' => $session->payment_status, // Will return 'paid' or 'unpaid'
                'payment_method' => $intent ? $intent->payment_method : null,
                'paid' => $session->payment_status === 'paid',
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Processes a refund for a payment.
     *
     * @param string $paymentId Stripe Payment Intent ID.
     * @param float $amount Amount to refund.
     * @return array Refund details including status and transaction IDs.
     * @throws PaymentGatewayException If refund creation fails.
     */
    public function refund(string $paymentId, float $amount): array
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentId,
                'amount' => (int) ($amount * 100),
            ]);

            return PaymentGatewayResponse::success('Refund processed successfully', [
                'refund_id' => $refund->id,
                'transaction_id' => $refund->payment_intent,
                'amount' => $refund->amount / 100,
                'currency' => $refund->currency,
                'status' => $refund->status,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }
}
