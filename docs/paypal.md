# PayPal Integration Documentation

This file provides detailed documentation for integrating PayPal Payment Gateway.

## Features

- PayPal payments
- Payment verification
- Refund processing
- Comprehensive error handling
- Sandbox and live environments

## Configuration

Create a configuration file for PayPal:

```php
// config/paypal.php
return [
    'client_id' => 'YOUR_CLIENT_ID',
    'client_secret' => 'YOUR_CLIENT_SECRET',
    'mode' => 'sandbox', // 'sandbox' for test or 'live' for production
    'success_url' => 'YOUR_SUCCESS_URL',
    'cancel_url' => 'YOUR_CANCEL_URL',
];
```

## Usage

### Initialize PayPal Gateway

```php
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

// Initialize PayPal Gateway
$config = include 'config/paypal.php';
$paypalConfig = [
    'client_id' => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'mode' => $config['mode'],
    'success_url' => $config['success_url'],
    'cancel_url' => $config['cancel_url'],
];

$paypalGateway = PaymentGatewayFactory::create('paypal', $paypalConfig);
```

### Create Payment

```php
// Create Payment
$paymentData = [
    'amount' => 100.00,
    'currency' => 'USD',
    'payment_method' => 'paypal',
    'description' => 'Test payment'
];

$result = $paypalGateway->createPayment($paymentData);
// Response includes:
// - payment_url (URL to redirect user for payment)
// - payment_id (PayPal payment ID for reference)
```

### Verify Payment

```php
// Verify payment using payment ID
$verificationResult = $paypalGateway->verifyPayment($paymentId);
// Response includes:
// - transaction_id
// - payment_id
// - amount
// - currency
// - payment_status
// - paid (boolean)
```

### Process Refund

```php
// Refund payment
$refundResult = $paypalGateway->refund($paymentId, 100.00);
// Response includes:
// - refund_id
// - payment_id
// - amount
// - currency
// - status
// - refunded (boolean)
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

Common exceptions:

- Configuration errors (400)
- Authentication errors (401)
- Validation errors (422)
- Payment processing errors (400)

## Testing

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run PayPal tests only
./vendor/bin/phpunit tests/Feature/PaypalPaymentTest.php
./vendor/bin/phpunit tests/Unit/PaypalGatewayTest.php
```

### Unit Tests

Unit tests cover individual components:

#### PaypalGatewayTest

- `testInitialization`: Verifies gateway initialization
- `testMissingConfig`: Tests error handling for missing configuration
- `testCreatePayment`: Validates payment creation with response format
- `testVerifyPayment`: Tests payment verification functionality
- `testRefund`: Ensures refund processing works correctly

### Feature Tests

Feature tests cover complete payment flows:

#### PaypalPaymentTest

- `testCompletePaymentFlow`: Tests full payment lifecycle
  - Payment creation
  - Payment verification
  - Refund processing
- Error scenario tests:
  - Invalid amount
  - Invalid currency
  - Missing required fields
  - Invalid payment ID
  - Invalid refund attempts

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

Maintaining this package takes time and effort. If you'd like to support its development and keep it growing, you can:

- 🌟 Star this repository
- 📢 Sharing it with others
- 🛠️ Contribute by reporting issues or suggesting features
- ☕ [Buy me a coffee](https://buymeacoffee.com/abdulbaset)
- ❤️ Become a sponsor on [GitHub Sponsors](https://github.com/sponsors/AbdulbasetRS)
- 💵 Make a one-time donation via [PayPal](https://paypal.me/abdulbasetrs)

Your support means a lot to me! Thank you for making this possible. 🙏
