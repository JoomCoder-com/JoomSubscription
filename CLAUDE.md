# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is JoomSubscription, a Joomla 5.x subscription management component for managing memberships, plans, payments, and access control. The codebase follows Joomla's MVC architecture with custom extensions for payment gateways, rules, and actions.

## Development Commands

### Local Development
Since this is a WAMP/Joomla installation:

```bash
# The site runs on local WAMP server at localhost
# Configuration: /configuration.php
# Database: MySQL (root/no password) on database 'joomsubscription'
# Table prefix: el1qe_
```

### CLI Access
```bash
# Run Joomla CLI commands
php cli/joomla.php
```

## Architecture & Key Components

### Component Structure (MVC Pattern)
- **Main Entry**: `/components/com_joomsubscription/joomsubscription.php` - Front-end entry point
- **API Layer**: `/components/com_joomsubscription/api.php` - Subscription API (JoomsubscriptionApi class)
- **Controllers**: `/components/com_joomsubscription/controllers/` - Handle requests (emplan, empayment, emsales, etc.)
- **Models**: `/components/com_joomsubscription/models/` - Business logic and data access
- **Views**: `/components/com_joomsubscription/views/` - Presentation layer
- **Tables**: `/components/com_joomsubscription/tables/` - Database table definitions

### Admin Interface
- Located in `/administrator/components/com_joomsubscription/`
- Manages plans, subscriptions, payments, fields, groups, taxes, and coupons

### Extension Libraries

**Payment Gateways** (`/components/com_joomsubscription/library/gateways/`):
- PayPal, Stripe, 2Checkout, Authorize.net, Skrill, and many others
- Each gateway has its own PHP class and XML configuration

**Rules System** (`/components/com_joomsubscription/library/rules/`):
- Content access control (com_content, com_k2, com_kunena)
- Default access rules
- Component-specific integrations

**Actions System** (`/components/com_joomsubscription/library/actions/`):
- Email integrations (AcyMail, MailChimp, GetResponse)
- CRM/Forum integrations (Kunena, EasySocial, JomSocial)
- Custom hooks and SQL actions

**Custom Fields** (`/components/com_joomsubscription/library/fields/`):
- Serial numbers, cross-sell, upsell, date fields
- Field types with their own templates and language files

### Database Tables (prefix: `#__joomsubscription_`)
- plans, subscriptions, history, sales
- coupons, coupon_history
- fields, groups, states, taxes
- invoiceto, actions, rules, serial

### Language Files
- Frontend: `/language/en-GB/com_joomsubscription*.ini`
- Backend: `/administrator/language/en-GB/com_joomsubscription*.ini`
- Each gateway/rule/action has separate language files

## Important Files & Locations

- **Configuration**: `/configuration.php` - Joomla configuration
- **Component Manifest**: `/administrator/components/com_joomsubscription/joomsubscription.xml`
- **Installation Script**: `/administrator/components/com_joomsubscription/install.php`
- **Access Control**: `/administrator/components/com_joomsubscription/access.xml`
- **Component Config**: `/administrator/components/com_joomsubscription/config.xml`

## API Usage

The main API class `JoomsubscriptionApi` provides methods like:
- `hasSubscription($plans, $msg, $user_id, $count, $redirect, $url)` - Check user subscription status
- Integration with Joomla's user system and access control

## Development Notes

1. **Joomla Version**: Running on Joomla 5.x with PHP 8.1+ requirement
2. **Debug Mode**: Currently enabled (`$debug = true` in configuration.php)
3. **Database**: Using MySQLi with no password for local development
4. **MVC Framework**: Uses Mint MVC framework (custom Joomla extension framework)
5. **Payment Processing**: Each gateway implements its own IPN/webhook handling
6. **Access Control**: Integrates with Joomla's ACL system

## Common Tasks

- Adding new payment gateway: Create folder in `/library/gateways/` with PHP class and XML config
- Adding new rule: Create folder in `/library/rules/` with component integration
- Adding new action: Create folder in `/library/actions/` with action handler
- Language updates: Edit appropriate `.ini` files in language folders
- Database updates: Handle in `install.php` script

## Testing Approach

No formal test suite found. Testing appears to be manual through:
- Joomla admin interface
- Frontend subscription flows
- Payment gateway sandbox modes

## Support Resources

- Component by JoomCoder (https://www.joomcoder.com/)
- Support email: support@mintjoomla.com
- Component appears to be commercial with GPL license