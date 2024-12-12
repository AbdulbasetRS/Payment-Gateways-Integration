<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Gateways;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts\PaypalGatewayAbstract;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Paypal\PaymentRequest;
use Abdulbaset\PaymentGatewaysIntegration\Responses\PaymentGatewayResponse;
use Abdulbaset\PaymentGatewaysIntegration\Utils\Collection;

/**
 * PaypalGateway Class
 *
 * This class provides integration with the PayPal payment gateway. It implements
 * the PaymentGatewayInterface and offers methods for creating payments,
 * verifying payments, and processing refunds.
 *
 * @see https://github.com/AbdulbasetRS/Payment-Gateways-Integration/blob/main/docs/paypal.md Additional documentation and source code on GitHub.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Gateways
 */
class PaypalGateway extends PaypalGatewayAbstract
{
    /**
     * Create a new PayPal payment.
     *
     * @param array $paymentData Data required to create a payment.
     * @return array Payment response containing approval URL and payment ID.
     * @throws PaymentGatewayException If payment creation fails.
     */
    public function createPayment(array $paymentData): array
    {
        $request = new PaymentRequest($paymentData);
        $validatedData = $request->validated();

        try {
            $response = $this->client->post('payments/payment', [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => $validatedData['payment_method'],
                ],
                'transactions' => [
                    [
                        'amount' => [
                            'total' => $validatedData['amount'],
                            'currency' => $validatedData['currency'],
                        ],
                        'description' => $validatedData['description'] ?? 'Payment transaction',
                    ],
                ],
                'redirect_urls' => [
                    'return_url' => $this->config['success_url'],
                    'cancel_url' => $this->config['cancel_url'],
                ],
            ]);

            if (!isset($response['id'])) {
                throw PaymentGatewayException::paymentError('Failed to create PayPal order');
            }

            $links = new Collection($response['links'] ?? []);
            $approveLink = $links->firstWhere('rel', 'approval_url');
            $approveUrl = $approveLink['href'] ?? null;

            if (!$approveUrl) {
                throw PaymentGatewayException::paymentError('PayPal approval URL not found');
            }

            return PaymentGatewayResponse::success('Payment URL generated successfully', [
                'payment_url' => $approveUrl,
                'payment_id' => $response['id'],
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }

    /**
     * Verify a payment based on its ID.
     *
     * @param string $paymentId The ID of the payment to verify.
     * @return array Verification details, including transaction info and status.
     * @throws PaymentGatewayException If verification fails.
     */
    public function verifyPayment(string $paymentId): array
    {

        try {
            $payment = $this->getPayment($paymentId);
            $payerId = $payment['payer']['payer_info']['payer_id'] ?? null;
            $payload = [
                'payer_id' => $payerId,
            ];

            $response = $this->client->post("payments/payment/{$paymentId}/execute", $payload);

            if ($response['state'] === 'approved') {
                // Payment Successful!
                $transaction = $response['transactions'][0];
                $relatedSale = $transaction['related_resources'][0]['sale'];

                return PaymentGatewayResponse::success('Payment verified successfully', [
                    'transaction_id' => $relatedSale['id'],
                    'payment_id' => $response['id'],
                    'amount' => $transaction['amount']['total'],
                    'currency' => $transaction['amount']['currency'],
                    'payment_status' => $relatedSale['state'],
                    'paid' => $relatedSale['state'] === 'completed',
                ]);
            }

            throw PaymentGatewayException::paymentError('payment faild or not approved.');

        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }

    }

    /**
     * Process a refund for a transaction.
     *
     * @param string $transactionId The ID of the transaction to refund.
     * @param float $amount The amount to refund.
     * @return array Refund details, including status and refunded amount.
     * @throws PaymentGatewayException If refund fails.
     */
    public function refund(string $transactionId, float $amount): array
    {
        $saleDetails = $this->client->get("payments/sale/{$transactionId}");
        $currency = $saleDetails['amount']['currency'] ?? null;
        if (!isset($currency)) {
            throw PaymentGatewayException::paymentError('Currency not found in sale details');
        }
        try {
            $payload['amount'] = [
                'total' => number_format($amount, 2, '.', ''),
                'currency' => $currency,
            ];

            $response = $this->client->post("payments/sale/{$transactionId}/refund", $payload);

            return PaymentGatewayResponse::success('Refund processed successfully', [
                'refund_id' => $response['id'],
                'payment_id' => $response['parent_payment'],
                'amount' => $response['amount']['total'],
                'currency' => $response['amount']['currency'],
                'status' => $response['state'],
                'refunded' => $response['state'] === 'completed',
                // 'response' => $response,
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }
}
