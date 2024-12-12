<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Exceptions;

use Abdulbaset\PaymentGatewaysIntegration\Responses\PaymentGatewayResponse;
use Exception;

/**
 * PaymentGatewayException Class
 *
 * A custom exception class to handle errors in payment gateway integrations.
 * Provides methods for handling different types of errors and returning responses in various formats such as arrays, objects, and JSON.
 *
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Exceptions
 */
class PaymentGatewayException extends Exception
{
    /**
     * The additional data related to the error.
     *
     * @var array
     */
    protected array $data;

    /**
     * The HTTP status code for the error.
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * Constructor to initialize the exception with a message, status code, additional data, and other optional parameters.
     *
     * @param string $message The error message.
     * @param int $statusCode The HTTP status code for the error.
     * @param array $data Additional data related to the error.
     * @param int $code The error code (optional).
     * @param Exception|null $previous A previous exception for chaining (optional).
     */
    public function __construct(string $message = "", int $statusCode = 400, array $data = [], int $code = 0, ?Exception $previous = null)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Creates a PaymentGatewayException for configuration errors.
     *
     * @param string $message The error message.
     * @param array $data Additional error data.
     * @return self A new PaymentGatewayException instance.
     */
    public static function configurationError(string $message, array $data = []): self
    {
        return new self($message, 400, $data);
    }

    /**
     * Creates a PaymentGatewayException for payment errors.
     *
     * @param string $message The error message.
     * @param array $data Additional error data.
     * @return self A new PaymentGatewayException instance.
     */
    public static function paymentError(string $message, array $data = []): self
    {
        return new self($message, 400, $data);
    }

    /**
     * Creates a PaymentGatewayException for validation errors.
     *
     * @param string $message The error message.
     * @param array $data Additional error data.
     * @return self A new PaymentGatewayException instance.
     */
    public static function validationError(string $message, array $data = []): self
    {
        return new self($message, 422, $data);
    }

    /**
     * Creates a PaymentGatewayException for authentication errors.
     *
     * @param string $message The error message.
     * @param array $data Additional error data.
     * @return self A new PaymentGatewayException instance.
     */
    public static function authenticationError(string $message, array $data = []): self
    {
        return new self($message, 401, $data);
    }

    /**
     * Retrieves the error response as an array.
     *
     * @return array The error response in array format.
     */
    public function getResponse(): array
    {
        return PaymentGatewayResponse::error(
            $this->getMessage(),
            $this->data,
            $this->statusCode
        );
    }

    /**
     * Retrieves the additional error data.
     *
     * @return array The error data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Converts the exception to an array format for the response.
     *
     * @return array The error response in array format.
     */
    public function toArray(): array
    {
        return PaymentGatewayResponse::error(
            $this->getMessage(),
            $this->data,
            $this->statusCode
        );
    }

    /**
     * Converts the exception to an object format for the response.
     *
     * @return object The error response as an object.
     */
    public function toObject(): object
    {
        return (object) PaymentGatewayResponse::error(
            $this->getMessage(),
            $this->data,
            $this->statusCode
        );
    }

    /**
     * Converts the exception to a JSON format for the response.
     *
     * @return string The error response as a JSON string.
     */
    public function toJson()
    {
        return json_encode(PaymentGatewayResponse::error(
            $this->getMessage(),
            $this->data,
            $this->statusCode
        ));
    }
}
