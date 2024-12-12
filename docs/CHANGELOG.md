# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-12

### Added

- Initial release with support for multiple payment gateways:
  - Paymob integration with card and mobile wallet payments
  - Stripe integration with card payments and webhooks
  - PayPal integration with standard payment flow
  - Tap payment gateway integration
- Comprehensive validation system for payment requests
- Standardized response format across all gateways
- Abstract classes for each payment gateway implementation
- Unit and feature tests for all payment gateways
- Configuration examples for all supported gateways
- Detailed documentation for each payment gateway:
  - Installation guides
  - Configuration instructions
  - Usage examples
  - API references
- Custom exception handling for payment operations
- PSR-4 autoloading support
- MIT License

### Security

- Secure authentication implementation for all payment gateways
- Input validation and sanitization
- Secure API key handling
- HTTPS enforcement for all API calls

[1.0.0]: https://github.com/AbdulbasetRS/Payment-Gateways-Integration/releases/tag/v1.0.0
