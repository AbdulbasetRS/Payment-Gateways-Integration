# Stripe Integration Documentation

This file provides detailed documentation for integrating Stripe.

## Features

- Card payments
- Subscription management
- Webhooks for payment events

## Configuration

Create a configuration file for Stripe:

```php
// config/stripe.example.php
return [
    'api_key' => 'YOUR_API_KEY',
    'secret_key' => 'YOUR_SECRET_KEY',
    'webhook_secret' => 'YOUR_WEBHOOK_SECRET',
    'success_url' => 'YOUR_SUCCESS_URL',
    'cancel_url' => 'YOUR_CANCEL_URL',
];
```

## Usage

### Stripe Integration

```php
$stripeConfig = [
    'secret_key' => 'YOUR_SECRET_KEY',
    'publishable_key' => 'YOUR_PUBLISHABLE_KEY'
];

$stripeGateway = PaymentGatewayFactory::create('stripe', $stripeConfig);

$paymentData = [
    'amount' => 100.00,
    'currency' => 'usd',
    'customerInfo' => [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]
];

$result = $stripeGateway->createPayment($paymentData);
```

### Payment Verification

```php
// Verify Stripe payment
$stripeVerification = $stripeGateway->verifyPayment($paymentIntentId);
```

### Refund Processing

```php
// Refund Stripe payment
$stripeRefund = $stripeGateway->refund($paymentIntentId, 100.00);
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

# Run specific test suite
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Feature

# Run Stripe unit tests only
./vendor/bin/phpunit --filter testCreateCardPayment tests/Unit/PaymobGatewayTest.php

./vendor/bin/phpunit tests/Unit/StripeGatewayTest.php

# Run Stripe feature tests only
./vendor/bin/phpunit tests/Feature/StripePaymentTest.php

```

### Unit Tests

Unit tests cover individual components:

#### StripeGatewayTest

- Similar test cases for Stripe gateway functionality
- Tests for payment creation, verification, and refunds

### Feature Tests

Feature tests cover complete payment flows:

#### StripePaymentTest

- `testCompletePaymentFlow`: Tests complete Stripe payment process
  - Payment intent creation
  - Payment verification
  - Refund processing

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
