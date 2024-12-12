<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Gateways;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\Abstracts\TapGatewayAbstract;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Requests\Tap\PaymentRequest;
use Abdulbaset\PaymentGatewaysIntegration\Responses\PaymentGatewayResponse;

/**
 * TapGateway Class
 *
 * This class provides integration with the Tap payment gateway. It implements the
 * PaymentGatewayInterface and offers methods for creating payments,
 * verifying payments, and processing refunds.
 *
 * @see https://github.com/AbdulbasetRS/Payment-Gateways-Integration/blob/main/docs/tap.md Additional documentation and source code on GitHub.
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Gateways
 */
class TapGateway extends TapGatewayAbstract
{
    /**
     * Creates a payment request with the Tap API.
     *
     * @param array $paymentData Details about the payment, such as amount, currency, and customer information.
     * @return array Response data including payment key, URL, and transaction details.
     * @throws PaymentGatewayException If the payment creation fails.
     */
    public function createPayment(array $paymentData): array
    {
        $request = new PaymentRequest($paymentData);
        $validatedData = $request->validated();

        try {
            $response = $this->client->post('charges', [
                'amount' => $validatedData['amount'],
                'currency' => $validatedData['currency'],
                'customer' => [
                    'first_name' => $validatedData['billing_data']['first_name'],
                    'last_name' => $validatedData['billing_data']['last_name'],
                    'email' => $validatedData['billing_data']['email'],
                    'phone' => [
                        'country_code' => '965',
                        'number' => $validatedData['billing_data']['phone_number'],
                    ],
                ],
                'source' => ['id' => 'src_card'],
                'redirect' => [
                    'url' => $this->config['success_url'],
                ],
                'post' => [
                    'url' => $this->config['success_url'],
                ],
            ]);

            return PaymentGatewayResponse::success('Payment URL generated successfully', [
                'payment_key' => $response['id'],
                'payment_url' => $response['transaction']['url'],
                'order_id' => $response['reference']['order'] ?? null,
                'transaction_id' => $response['id'],
                'response' => $response,
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }

    /**
     * Verifies the status of a payment using its ID.
     *
     * @param string $paymentId The unique identifier for the payment.
     * @return array Response data including transaction ID, status, and amount.
     * @throws PaymentGatewayException If the payment verification fails.
     */
    public function verifyPayment(string $paymentId): array
    {
        try {
            $response = $this->client->get("charges/{$paymentId}");

            return PaymentGatewayResponse::success('Payment verified successfully', [
                'transaction_id' => $response['id'],
                'order_id' => $response['reference']['order'] ?? null,
                'amount' => $response['amount'],
                'currency' => $response['currency'],
                'payment_status' => $response['status'],
                'paid' => $response['status'] === 'CAPTURED',
                // 'response' => $response,
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }

    /**
     * Processes a refund for a payment.
     *
     * @param string $paymentId The unique identifier of the payment to refund.
     * @param float $amount The amount to refund.
     * @return array Response data including refund ID, status, and refunded amount.
     * @throws PaymentGatewayException If the refund fails.
     */
    public function refund(string $paymentId, float $amount): array
    {
        try {
            $response = $this->client->post("refunds", [
                'charge_id' => $paymentId,
                'amount' => $amount,
                'currency' => 'USD',
                'reason' => 'Requested by customer',
                'reference' => [
                    'merchant' => 'txn_' . time(),
                ],
            ]);

            return PaymentGatewayResponse::success('Refund processed successfully', [
                'refund_id' => $response['id'],
                'transaction_id' => $response['charge']['id'],
                'amount' => $response['amount'],
                'currency' => $response['currency'],
                'status' => $response['status'],
            ]);
        } catch (PaymentGatewayException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage(), $e->getData());
        }
    }
}
