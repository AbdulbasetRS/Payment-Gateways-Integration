<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Gateways;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts\PaymobGatewayAbstract;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Paymob\PaymentRequest;
use Abdulbaset\PaymentGatewaysIntegration\Responses\PaymentGatewayResponse;

/**
 * PaymobGateway Class
 *
 * This class provides integration with the Paymob payment gateway. It implements
 * the PaymentGatewayInterface and provides methods for creating payments,
 * verifying payments, and processing refunds.
 *
 * @see https://github.com/AbdulbasetRS/Payment-Gateways-Integration/blob/main/docs/paymob.md Additional documentation and source code on GitHub.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Gateways
 */
class PaymobGateway extends PaymobGatewayAbstract
{
    /**
     * Creates a payment request with the specified data.
     *
     * @param array $paymentData The payment data, including amount, currency, and billing details.
     * @return array A response containing the payment URL and related details.
     * @throws PaymentGatewayException If payment creation fails.
     */
    public function createPayment(array $paymentData): array
    {
        $request = new PaymentRequest($paymentData);
        $validatedData = $request->validated();

        try {
            // Step 1: Create order
            $orderResponse = $this->client->post('ecommerce/orders', [
                'amount_cents' => $validatedData['amount'] * 100,
                'currency' => $validatedData['currency'],
                'delivery_needed' => false,
                'items' => $validatedData['items'] ?? [],
            ]);

            if (!isset($orderResponse['id'])) {
                throw PaymentGatewayException::paymentError('Failed to create order');
            }

            // Step 2: Create payment token
            $paymentResponse = $this->client->post('acceptance/payment_keys', [
                'amount_cents' => $validatedData['amount'] * 100,
                'currency' => $validatedData['currency'],
                'order_id' => $orderResponse['id'],
                'billing_data' => $validatedData['billing_data'],
                'payment_methods' => $validatedData['payment_methods'] ?? ['card', 'wallet'],
                'integration_id' => $this->config['integration_id'],
                'lock_order_when_paid' => true,
            ]);

            if (!isset($paymentResponse['token'])) {
                throw PaymentGatewayException::paymentError('Failed to create payment token');
            }

            // Step 3: Generate payment URL based on payment method
            $paymentMethod = $validatedData['payment_method'] ?? 'card';

            if ($paymentMethod === 'wallet') {
                $result = $this->createWalletPayment($paymentResponse['token'], $validatedData['billing_data']['phone_number']);
            } else {
                $result = [
                    'payment_key' => $paymentResponse['token'],
                    'order_id' => $orderResponse['id'],
                    'transaction_id' => $orderResponse['id'],
                    'payment_url' => $this->getPaymentUrl($paymentMethod, $paymentResponse['token']),
                ];
            }

            return PaymentGatewayResponse::success('Payment URL generated successfully', $result);
        } catch (\Exception $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Verifies the status of a payment transaction.
     *
     * @param string $paymentId The ID of the payment transaction.
     * @return array The verification details.
     * @throws PaymentGatewayException If payment verification fails.
     */
    public function verifyPayment(string $paymentId): array
    {
        try {
            $response = $this->client->get("acceptance/transactions/{$paymentId}");

            if (isset($response['id'])) {
                return PaymentGatewayResponse::success('Payment verified successfully', [
                    'transaction_id' => $response['id'],
                    'order_id' => $response['order']['id'] ?? null,
                    'amount' => $response['amount_cents'] / 100,
                    'currency' => $response['currency'],
                    'payment_status' => $response['success'] ? 'paid' : 'unpaid',
                    'paid' => $response['success'] ? true : false,
                ]);
            }

            throw PaymentGatewayException::paymentError('Payment verification failed');
        } catch (\Exception $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Processes a refund for a payment transaction.
     *
     * @param string $paymentId The ID of the payment transaction.
     * @param float $amount The amount to refund.
     * @return array The refund details.
     * @throws PaymentGatewayException If the refund fails.
     */
    public function refund(string $paymentId, float $amount): array
    {
        try {
            $response = $this->client->post('acceptance/void_refund/refund', [
                'transaction_id' => $paymentId,
                'amount_cents' => $amount * 100,
            ]);

            return PaymentGatewayResponse::success('Refund processed successfully', $response);
        } catch (\Exception $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }
}
