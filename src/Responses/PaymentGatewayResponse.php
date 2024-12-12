<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Responses;

/**
 * PaymentGatewayResponse Class
 *
 * This class is responsible for formatting API responses for payment gateway operations.
 * It provides utility methods to generate consistent success and error responses with a
 * structured format.
 *
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Responses
 */
class PaymentGatewayResponse
{
    /**
     * HTTP status code of the response.
     *
     * @var int
     */
    private int $status;

    /**
     * Message describing the status of the response.
     *
     * @var string
     */
    private string $message;

    /**
     * Additional data included in the response, if any.
     *
     * @var array|null
     */
    private ?array $data;

    /**
     * Constructor to initialize the response object.
     *
     * @param int $status HTTP status code (e.g., 200 for success, 400 for error).
     * @param string $message A brief description of the response.
     * @param array|null $data Optional data to include in the response.
     */
    public function __construct(int $status = 200, string $message = 'Success', ?array $data = null)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $this->sortData($data);
    }

    /**
     * Creates a standardized success response.
     *
     * @param string $message Custom success message (default: "Success").
     * @param array|null $data Optional data to include in the success response.
     * @return array Formatted success response.
     */
    public static function success(string $message = 'Success', ?array $data = null): array
    {
        return (new self(200, $message, $data))->toArray();
    }

    /**
     * Creates a standardized error response.
     *
     * @param string $message Custom error message (default: "Error").
     * @param array|null $data Optional data to include in the error response.
     * @param int $status HTTP status code for the error (default: 400).
     * @return array Formatted error response.
     */
    public static function error(string $message = 'Error', ?array $data = null, int $status = 400): array
    {
        return (new self($status, $message, $data))->toArray();
    }

    /**
     * Converts the response object to an associative array.
     *
     * @return array Array representation of the response.
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }

    /**
     * Sorts the response data by keys in ascending order.
     *
     * @param array|null $data The data to be sorted (optional).
     * @return array|null Sorted data or null if input data is null.
     */
    private function sortData(?array $data): ?array
    {
        if (!is_array($data)) {
            return $data;
        }

        ksort($data); // Sort by keys in ascending order
        return $data;
    }
}
