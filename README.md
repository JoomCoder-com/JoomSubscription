# JoomSubscription

A comprehensive subscription management component for Joomla CMS that handles memberships, payment processing, and content access control.

## Overview

JoomSubscription is a powerful Joomla component designed to manage subscription-based access to your website content. It provides flexible membership plans, multiple payment gateway integrations, and sophisticated access control rules.

## Features

### Core Functionality
- **Subscription Plans Management** - Create and manage unlimited subscription plans with various pricing models
- **User Subscription Tracking** - Complete history and analytics of user subscriptions
- **Access Control** - Rule-based content restriction system
- **Coupon System** - Discount codes and promotional campaigns
- **Tax Management** - Configurable tax rates by region
- **Invoice Generation** - Automatic invoice creation and management

### Payment Gateway Integrations
- PayPal & PayPal Express Checkout
- Stripe
- Authorize.net
- 2Checkout
- Skrill (Moneybookers)
- Offline payments
- And 15+ other payment gateways

### Third-Party Integrations
- **Email Marketing**: MailChimp, GetResponse, AcyMail
- **Social/Community**: JomSocial, EasySocial, Kunena Forum
- **Content**: K2, standard Joomla articles
- **E-commerce**: HikaShop integration

## Requirements

- Joomla 5.x
- PHP 8.1 or higher
- MySQL 5.7+ / MariaDB 10.2+
- Modern web browser with JavaScript enabled

## Installation

1. Download the latest release package
2. In Joomla Administrator, go to **System → Install → Extensions**
3. Upload and install the component package
4. Navigate to **Components → JoomSubscription** to configure

## Configuration

### Initial Setup
1. Configure general settings in component options
2. Set up payment gateways with your merchant credentials
3. Create subscription plans
4. Configure access rules for content restriction
5. Set up tax rates if applicable

### API Usage

The component provides a PHP API for developers:

```php
// Check if user has active subscription
$hasAccess = JoomsubscriptionApi::hasSubscription(
    $plan_ids,     // Array of plan IDs
    $message,      // Message to show if no access
    $user_id,      // User ID (0 for current user)
    $count,        // Track usage
    $redirect      // Auto-redirect if no access
);
```

## File Structure

```
/components/com_joomsubscription/    # Frontend component files
    /controllers/                    # Request handlers
    /models/                         # Business logic
    /views/                          # Display templates
    /library/                        # Extensions library
        /gateways/                   # Payment gateway integrations
        /rules/                      # Access control rules
        /actions/                    # Subscription actions
        /fields/                     # Custom field types
/administrator/components/            # Backend administration
/language/                           # Language files
/media/mint/                         # Assets and resources
```

## Development

### Adding Custom Payment Gateway
1. Create new folder in `/library/gateways/your_gateway/`
2. Implement gateway PHP class extending base gateway
3. Add XML configuration file
4. Create language strings

### Adding Custom Rules
1. Create folder in `/library/rules/your_rule/`
2. Implement rule logic class
3. Add XML configuration

### Adding Custom Actions
1. Create folder in `/library/actions/your_action/`
2. Implement action handler
3. Configure trigger events

## Support

- **Documentation**: Available in component admin area
- **Issues**: Please report bugs via GitHub Issues
- **Website**: https://www.joomcoder.com/
- **Email**: support@joomcoder.com

## License

This component is released under the GNU General Public License v2.0 or later.

## Credits

Developed by JoomCoder - Professional Joomla Extensions Development

## Changelog

See recent commit history for latest changes and updates.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or report issues.

### Development Setup
1. Clone the repository
2. Set up local Joomla development environment
3. Install component via Joomla installer
4. Enable debug mode for development

## Security

If you discover any security-related issues, please email support@joomcoder.com instead of using the issue tracker.

---

**Note**: This is a commercial component with GPL license. While the code is open source, commercial support and updates are available from JoomCoder.