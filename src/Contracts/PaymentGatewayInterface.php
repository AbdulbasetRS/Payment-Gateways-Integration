<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Contracts;

/**
 * PaymentGatewayInterface
 *
 * This interface defines the contract for implementing payment gateway integrations.
 * Any payment gateway class that adheres to this interface must implement the
 * methods defined here, ensuring consistency and interoperability across different
 * payment gateway providers.
 *
 * Methods:
 * - `initialize(array $config)`: Initializes the payment gateway with necessary configurations like API keys and endpoints.
 * - `createPayment(array $paymentData)`: Creates a payment request with the specified payment details.
 * - `verifyPayment(string $paymentId)`: Verifies the status of a payment using its unique identifier.
 * - `refund(string $paymentId, float $amount)`: Processes a refund for a specified payment and amount.
 *
 * This interface facilitates a standardized approach to handling payment operations,
 * making it easier to integrate multiple payment gateways within the same application.
 *
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Contracts
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize the payment gateway with necessary configurations.
     *
     * @param array $config Configuration details such as API keys, endpoints, etc.
     */
    public function initialize(array $config): void;

    /**
     * Create a payment request with the given data.
     *
     * @param array $paymentData Payment details, including amount, currency, and payer information.
     * @return array Response containing payment details and status.
     */
    public function createPayment(array $paymentData): array;

    /**
     * Verify the status of a payment using its unique ID.
     *
     * @param string $paymentId Unique identifier of the payment to be verified.
     * @return array Response containing payment verification details and status.
     */
    public function verifyPayment(string $paymentId): array;

    /**
     * Process a refund for a given payment.
     *
     * @param string $paymentId Unique identifier of the payment to be refunded.
     * @param float $amount Amount to be refunded.
     * @return array Response containing refund details and status.
     */
    public function refund(string $paymentId, float $amount): array;
}
