# Paymob Integration Documentation

This file provides detailed documentation for integrating Paymob.

## Features

- Card payments
- Mobile wallet payments

## Configuration

Create a configuration file for Paymob:

```php
// config/paymob.php
return [
    'api_key' => 'YOUR_API_KEY',
    'username' => 'YOUR_USERNAME',
    'password' => 'YOUR_PASSWORD',
    'Iframe_1' => 'YOUR_IFRAME_ID',
    'mobile_wallets' => 'YOUR_MOBILE_WALLETS_INTEGRATION_ID',
    'online_card' => 'YOUR_ONLINE_CARD_INTEGRATION_ID',
];
```

## Usage

### Paymob Card Payment

```php
use Abdulbaset\PaymentGatewaysIntegration\PaymentGatewayFactory;

// Initialize Paymob Gateway
$config = include 'config/paymob.php';
$paymobConfig = [
    'api_key' => $config['api_key'],
    'integration_id' => $config['online_card'],
    'iframe_id' => $config['Iframe_1']
];
$paymobGateway = PaymentGatewayFactory::create('paymob', $paymobConfig);

// Create Card Payment
$cardPaymentData = [
    'amount' => 100.00,
    'currency' => 'EGP',
    'payment_method' => 'card',
    'billing_data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone_number' => '01000000000',
        'apartment' => '123',
        'building' => '123',
        'street' => 'Test St',
        'floor' => '1',
        'city' => 'Cairo',
        'state' => 'Cairo',
        'country' => 'EG'
    ]
];

$result = $paymobGateway->createPayment($cardPaymentData);
// Response includes:
// - payment_key
// - payment_url
// - order_id
// - transaction_id

```

### Paymob Mobile Wallet Payment

```php
$walletConfig = [
    'api_key' => $config['api_key'],
    'integration_id' => $config['mobile_wallets']
];

$walletGateway = PaymentGatewayFactory::create('paymob', $walletConfig);

$walletPaymentData = [
    'amount' => 100.00,
    'currency' => 'EGP',
    'payment_method' => 'wallet',
    'billing_data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone_number' => '01000000000', // Must be 11 digits for wallet payments
        'apartment' => '123',
        'building' => '123',
        'street' => 'Test St',
        'floor' => '1',
        'city' => 'Cairo',
        'state' => 'Cairo',
        'country' => 'EG'
    ]
];

$result = $walletGateway->createPayment($walletPaymentData);
// Response includes:
// - payment_key
// - payment_url
// - order_id
// - transaction_id
```

### Response Format

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

### Payment Verification

```php
// Verify Paymob payment
$verificationResult = $paymobGateway->verifyPayment($transactionId);
```

### Refund Processing

```php
// Refund Paymob payment
$refundResult = $paymobGateway->refund($transactionId, 100.00);
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

# Run Paymob unit tests only
./vendor/bin/phpunit tests/Unit/PaymobGatewayTest.php

# Run Paymob feature tests only
./vendor/bin/phpunit tests/Feature/PaymobPaymentTest.php
```

### Unit Tests

Unit tests cover individual components:

#### PaymobGatewayTest

- `testInitialization`: Verifies gateway initialization
- `testMissingConfig`: Tests error handling for missing configuration
- `testCreateCardPayment`: Validates card payment creation with response format

### Feature Tests

Feature tests cover complete payment flows:

#### PaymobPaymentTest

- `testCompleteCardPaymentFlow`: Tests full card payment process
  - Payment creation with response validation
  - Payment verification
  - Refund processing
- `testCompleteWalletPaymentFlow`: Tests mobile wallet payment flow
- Error scenario tests:
  - Invalid API key
  - Invalid amount
  - Invalid currency
  - Missing billing data
  - Invalid phone number

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
