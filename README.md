# WP Admin Dashboard Optimizer (WPADO)

WordPress plugin for secure gift card liquidation with Plaid RTP/FedNow payouts and Authorize.Net payment processing.

## Features

- **Federal Compliance**: Enforces $500/24h, $1,500/7d, $2,500/month, $8,500/year limits
- **Secure Banking**: Plaid OAuth 2.1 integration with RTP/FedNow capability checking
- **Payment Processing**: Authorize.Net integration via WS Form PRO extension
- **Multi-Stage Security**: 5 secret validation checkpoints with AES-256 encryption
- **Role-Based Workflow**: Subscriber → Plaid User → Transaction User → PAYMENT → Subscriber
- **Comprehensive Logging**: Transaction history, error logs, payout tracking with reconciliation

## Requirements

- WordPress 6.0+
- PHP 8.0+
- WS Form PRO with Authorize Accept extension
- JetEngine for dynamic dashboards
- MySQL 5.7+

## Installation

1. Upload plugin to `/wp-content/plugins/` directory
2. Run `composer install` for Plaid SDK
3. Activate plugin through WordPress admin
4. Configure API credentials in Settings

## Architecture

See `8:19 PRD.md` for complete technical specifications and implementation details.

## Development

Branch strategy: Each phase/sub-phase gets its own branch for safe development and rollback capability.

## Documentation

- `8:19 PRD.md` - Complete Product Requirements Document
- `wordpress_site_info.md` - WordPress environment details
- `complete_customer_journey_mermaid_diagram.png` - Visual workflow
- `wsf-form-*.json` - WS Form configurations for each step