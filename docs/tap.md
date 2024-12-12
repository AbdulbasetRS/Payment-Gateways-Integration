# Tap Integration Documentation

This file provides detailed documentation for integrating Tap Payment Gateway.

## Features

- Card payments
- Payment verification
- Refund processing

## Configuration

Create a configuration file for Tap:

```php
// config/tap.php
return [
    'secret_key' => 'YOUR_SECRET_KEY',
    'publishable_key' => 'YOUR_PUBLISHABLE_KEY',
    'success_url' => 'YOUR_SUCCESS_URL',
    'cancel_url' => 'YOUR_CANCEL_URL',
];
```

## Usage

### Tap Card Payment

```php
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

// Initialize Tap Gateway
$config = include 'config/tap.php';
$tapConfig = [
    'secret_key' => $config['secret_key'],
    'publishable_key' => $config['publishable_key'],
    'success_url' => $config['success_url'],
    'cancel_url' => $config['cancel_url'],
];

$tapGateway = PaymentGatewayFactory::create('tap', $tapConfig);

// Create Card Payment
$paymentData = [
    'amount' => 100.00,
    'currency' => 'USD',
    'payment_method' => 'card',
    'billing_data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone_number' => '12345678901'
    ]
];

$result = $tapGateway->createPayment($paymentData);
// Response includes:
// - payment_key
// - payment_url
// - order_id
// - transaction_id
```

### Payment Verification

```php
// Verify Tap payment
$verificationResult = $tapGateway->verifyPayment($paymentId);
```

### Refund Processing

```php
// Refund Tap payment
$refundResult = $tapGateway->refund($paymentId, 100.00);
```

## Error Handling

The package uses custom exceptions for different error scenarios:

```php
try {
    $result = $gateway->createPayment($paymentData);
} catch (PaymentGatewayException $e) {
    $errorResponse = $e->getResponse();
    // Error response format:
    // [
    //     'status' => $statusCode, // e.g., 400, 401, 422
    //     'message' => 'Error message',
    //     'data' => [] // Additional error details
    // ]
}
```

## Response Format

All gateway responses follow a standardized format:

```php
[
    'status' => 200, // HTTP status code
    'message' => 'Success message',
    'data' => [
        // Response data specific to the operation
    ]
]
```

## Testing

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run Tap tests only
./vendor/bin/phpunit tests/Feature/TapPaymentTest.php
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](../LICENSE) file for details.

## Support

For support:

- Email: AbdulbasetRedaSayedHF@Gmail.com
- Create an issue in the GitHub repository

## Donations 💖

Maintaining this package takes time and effort. If you’d like to support its development and keep it growing, you can:

- 🌟 Star this repository
- 📢 Sharing it with others
- 🛠️ Contribute by reporting issues or suggesting features
- ☕ [Buy me a coffee](https://buymeacoffee.com/abdulbaset)
- ❤️ Become a sponsor on [GitHub Sponsors](https://github.com/sponsors/AbdulbasetRS)
- 💵 Make a one-time donation via [PayPal](https://paypal.me/abdulbasetrs)

Your support means a lot to me! Thank you for making this possible. 🙏
