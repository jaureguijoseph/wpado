# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin called "WP Admin Dashboard Optimizer" that facilitates secure gift card liquidation. The plugin integrates with Plaid for bank account linking/payouts and Authorize.Net for payment processing, implementing a multi-role workflow with federal compliance limits and comprehensive security measures.

## Architecture

### Core Components
- **RoleManager**: Manages user role transitions (Subscriber → Plaid User → Transaction User → PAYMENT → Subscriber)
- **TokenManager**: Handles OAuth token lifecycle with AES-256 encryption
- **TransactionManager**: Processes gift card transactions with federal limit enforcement
- **PayoutManager**: Handles payouts via Plaid RTP or FedNow
- **Security Layer**: Multiple secret validation checks, encryption, hidden usernames

### Database Schema
Custom tables required:
- `wpado_plugin_transactions`: Transaction records with gross/net amounts, fees, reconciliation status
- `wpado_plugin_error_logs`: Comprehensive error logging with phase tracking
- `wpado_plugin_payout_log`: Payout processing records with retry mechanisms

### Federal Compliance Limits
Server-side enforcement of liquidation limits:
- $500 in 24 hours
- $1,500 in 7 days  
- $2,500 month-to-date
- $8,500 year-to-date

## Development Commands

Since this is a WordPress plugin, standard WordPress development practices apply:

### Testing
```bash
# Install WordPress test environment (if bin/install-wp-tests.sh exists)
./bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Run PHPUnit tests (if phpunit.xml exists)
phpunit
```

### Code Quality
```bash
# PHP CodeSniffer (WordPress standards)
phpcs --standard=WordPress .

# PHP Mess Detector
phpmd . text cleancode,codesize,controversial,design,naming,unusedcode
```

### Composer Dependencies
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

## Key Security Requirements

### Multi-Stage Secret Validation
The plugin implements 5 secret validation checkpoints:
1. After initial form submission
2. After bank account linking
3. Before Authorize.Net payment processing (PRE-PAYMENT SECURITY)
4. Before payout initiation (FINAL VERIFICATION)
5. During reconciliation process

### Encryption Standards
- All sensitive data encrypted with AES-256-CBC
- Hidden usernames generated server-side with `wp_generate_password()`
- OAuth tokens encrypted before database storage
- 90-day encryption key rotation cycle

### Error Handling
- Comprehensive error logging with phase tracking
- Automatic retry mechanisms (3 attempts with exponential backoff)
- Admin notifications for failed operations
- User role reversion on any error condition

## API Integrations

### Plaid Integration
- OAuth 2.1 Authorization Code flow
- Bank account linking via Plaid Link
- Identity verification requirements
- RTP/FedNow capability checking
- Webhook signature validation (HMAC SHA-256)

### Authorize.Net Integration  
- Accept.js for secure tokenization
- Transaction reporting via XML API
- Webhook signature validation (SHA-512)
- Real-time payment status updates

## Development Notes

### Test-Driven Development
The project documentation emphasizes TDD with Red-Green-Refactor cycle. All new code should follow this pattern with comprehensive test coverage.

### WordPress Integration
- Uses WS Form Pro for form handling
- JetEngine integration for dashboard display
- Custom user roles and capabilities
- WordPress Cron for background tasks
- `dbDelta` for database migrations

### Rate Limiting
- Plaid API: 5 requests/user/hour for public tokens, 10/day for access tokens
- Authorize.Net: 3 transactions/minute per user
- Implemented via user meta storage with timestamp tracking

## File Structure (Planned)

Based on the documentation, the intended structure is:
```
includes/
├── Core/                    # Main plugin logic
├── Security/                # OAuth, encryption, authentication
├── Database/                # Schema, migrations, models
├── API/                     # Plaid, Authorize.Net integrations
├── Notifications/           # Email/SMS notifications
├── ErrorHandlers/           # Specialized error handling
├── Admin/                   # WordPress admin interface
└── Utils/                   # Logging, validation, rate limiting

tests/                       # Complete test suite
assets/                      # Public CSS/JS/images
languages/                   # i18n translations
```

## Current State

The repository currently contains only documentation (PRD) and WS Form configurations. The actual PHP implementation needs to be built according to the detailed specifications in the PRD documentation.