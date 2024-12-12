<?php

namespace Abdulbaset\PaymentGatewaysIntegration\Clients;

use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * PaymentGatewayClient Class
 *
 * A client class for making HTTP requests to payment gateways using the Guzzle HTTP client.
 * This class provides methods for performing CRUD operations (POST, GET, PUT, DELETE) with configurable headers and data.
 * It handles sending requests to the payment gateway APIs and managing responses.
 *
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration\Clients
 */
class PaymentGatewayClient
{
    /**
     * @var Client Guzzle HTTP client instance.
     */
    private Client $client;

    /**
     * @var array Default request headers for API calls.
     */
    private array $headers;

    /**
     * @var string Base URL for the API endpoint.
     */
    private string $baseUrl;

    /**
     * Constructor for PaymentGatewayClient.
     *
     * @param string $baseUrl The base URL of the API.
     * @param array $headers The headers to be used in the requests.
     * @param array $options Additional options for the client configuration.
     */
    public function __construct(string $baseUrl = '', array $headers = [], array $options = [])
    {
        $this->baseUrl = $baseUrl;
        $this->headers = $headers;

        $defaultOptions = [
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
            'headers' => $this->headers,
        ];

        $clientOptions = array_merge($defaultOptions, $options);
        $this->client = new Client($clientOptions);
    }

    /**
     * Set a custom header for API requests.
     *
     * @param string $key The header key.
     * @param string $value The value of the header.
     */
    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Set the base URL for the API.
     *
     * @param string $baseUrl The new base URL to be used.
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
        $this->client = new Client(['base_uri' => $baseUrl]);
    }

    /**
     * Perform a POST request to the given endpoint.
     *
     * @param string $endpoint The API endpoint.
     * @param array|string $data The data to be sent with the request.
     * @param array $headers Additional headers for the request.
     * @param bool $isFormData Whether the data should be sent as form data or JSON.
     * @return array The response data from the API.
     * @throws PaymentGatewayException If the request fails or returns an error.
     */
    public function post(string $endpoint, array | string $data = [], array $headers = [], bool $isFormData = false): array
    {
        try {
            $requestHeaders = array_merge($this->headers, $headers);
            $options = [
                'headers' => $requestHeaders,
            ];

            if ($isFormData) {
                $options['body'] = $data;
            } else {
                $options['json'] = $data;
            }

            $response = $this->client->post($endpoint, $options);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($statusCode >= 400) {
                throw PaymentGatewayException::paymentError(
                    $responseData['message'] ?? $responseData['error_description'] ?? 'Request failed',
                    $responseData ?? [$response]
                );
            }

            return $responseData;
        } catch (GuzzleException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Perform a GET request to the given endpoint.
     *
     * @param string $endpoint The API endpoint.
     * @param array $query The query parameters to be included in the request.
     * @param array $headers Additional headers for the request.
     * @return array The response data from the API.
     * @throws PaymentGatewayException If the request fails or returns an error.
     */
    public function get(string $endpoint, array $query = [], array $headers = []): array
    {
        try {
            $requestHeaders = array_merge($this->headers, $headers);
            $response = $this->client->get($endpoint, [
                'headers' => $requestHeaders,
                'query' => $query,
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($statusCode >= 400) {
                throw PaymentGatewayException::paymentError(
                    $responseData['message'] ?? 'Request failed',
                    $responseData
                );
            }

            return $responseData;
        } catch (GuzzleException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Perform a PUT request to the given endpoint.
     *
     * @param string $endpoint The API endpoint.
     * @param array $data The data to be sent with the request.
     * @param array $headers Additional headers for the request.
     * @return array The response data from the API.
     * @throws PaymentGatewayException If the request fails or returns an error.
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        try {
            $requestHeaders = array_merge($this->headers, $headers);
            $response = $this->client->put($endpoint, [
                'headers' => $requestHeaders,
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($statusCode >= 400) {
                throw PaymentGatewayException::paymentError(
                    $responseData['message'] ?? 'Request failed',
                    $responseData
                );
            }

            return $responseData;
        } catch (GuzzleException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }

    /**
     * Perform a DELETE request to the given endpoint.
     *
     * @param string $endpoint The API endpoint.
     * @param array $headers Additional headers for the request.
     * @return array The response data from the API.
     * @throws PaymentGatewayException If the request fails or returns an error.
     */
    public function delete(string $endpoint, array $headers = []): array
    {
        try {
            $requestHeaders = array_merge($this->headers, $headers);
            $response = $this->client->delete($endpoint, [
                'headers' => $requestHeaders,
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($statusCode >= 400) {
                throw PaymentGatewayException::paymentError(
                    $responseData['message'] ?? 'Request failed',
                    $responseData
                );
            }

            return $responseData;
        } catch (GuzzleException $e) {
            throw PaymentGatewayException::paymentError($e->getMessage());
        }
    }
}
