<?php

namespace Abdulbaset\PaymentGatewaysIntegration;

use Abdulbaset\PaymentGatewaysIntegration\Contracts\PaymentGatewayInterface;
use Abdulbaset\PaymentGatewaysIntegration\Exceptions\PaymentGatewayException;
use Abdulbaset\PaymentGatewaysIntegration\Gateways\PaymobGateway;
use Abdulbaset\PaymentGatewaysIntegration\Gateways\PaypalGateway;
use Abdulbaset\PaymentGatewaysIntegration\Gateways\StripeGateway;
use Abdulbaset\PaymentGatewaysIntegration\Gateways\TapGateway;

/**
 * PaymentGatewayFactory Class
 *
 * A factory class for creating instances of different payment gateways.
 * This class abstracts the creation logic of various payment gateways,
 * allowing for a consistent interface to initialize and use them.
 *
 * @link https://github.com/AbdulbasetRS/Payment-Gateways-Integration Link to the GitHub repository for more details.
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed Link to my LinkedIn profile for professional inquiries.
 * @author Abdulbaset R. Sayed
 * @license MIT License
 * @package Abdulbaset\PaymentGatewaysIntegration
 */
class PaymentGatewayFactory
{
    /**
     * Mapping of supported gateway identifiers to their corresponding classes.
     *
     * @var array<string, string> An associative array where the key is the gateway identifier
     *                            and the value is the fully qualified class name of the gateway.
     */
    private static array $gateways = [
        'paymob' => PaymobGateway::class,
        'stripe' => StripeGateway::class,
        'tap' => TapGateway::class,
        'paypal' => PaypalGateway::class,
    ];

    /**
     * Create an instance of the specified payment gateway.
     *
     * @param string $gateway The identifier of the gateway to create (e.g., 'stripe', 'paypal').
     * @param array $config The configuration array required to initialize the gateway.
     * @return PaymentGatewayInterface An instance of the specified payment gateway.
     * @throws PaymentGatewayException If the specified gateway is not supported.
     */
    public static function create(string $gateway, array $config): PaymentGatewayInterface
    {
        if (!isset(self::$gateways[$gateway])) {
            throw PaymentGatewayException::configurationError("Unsupported gateway: {$gateway}");
        }
        // Retrieve the gateway class name and instantiate it
        $gatewayClass = self::$gateways[$gateway];
        $instance = new $gatewayClass();

        // Initialize the gateway with the provided configuration
        $instance->initialize($config);

        return $instance;
    }
}
