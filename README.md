```
 _   _      _                      _     _   ____       _                 
| \ | | ___| |___      _____  _ __| | __| | |  _ \  ___| |__  _   _  __ _ 
|  \| |/ _ \ __\ \ /\ / / _ \| '__| |/ _` | | | | |/ _ \ '_ \| | | |/ _` |
| |\  |  __/ |_ \ V  V / (_) | |  | | (_| | | |_| |  __/ |_) | |_| | (_| |
|_| \_|\___|\__| \_/\_/ \___/|_|  |_|\__,_| |____/ \___|_.__/ \__,_|\__, |
                                                                    |___/ 

```

# Networld_Debug Magento 2 Module

## Overview

This developer-focused debugging module is designed to assist with checkout and email debugging in Magento 2. It safely logs key data without interfering with production performance or stability.

---

## Features

- ✅ Logs email subject and recipients using `CaptureFinalEmail` plugin
- ✅ Logs order, grand total, shipping method, and delivery date via `TransportTemplateVarsLogger`
- ✅ Catches and logs checkout errors via `CatchOrderPlaceException`
- ✅ Adds HTML comments around rendered blocks for frontend debugging (`BlockComment`)
- ✅ Includes a placeholder CLI command for future layout/template hint tools

---

## File Highlights

| File | Purpose |
|------|---------|
| `Plugin/CaptureFinalEmail.php` | Logs email metadata when Magento sends the final message |
| `Plugin/TransportTemplateVarsLogger.php` | Logs `templateVars` passed to transactional email templates |
| `Plugin/CatchOrderPlaceException.php` | Logs any exceptions thrown during checkout order placement |
| `Plugin/BlockComment.php` | Wraps frontend block output in HTML comments for dev inspection |
| `Console/Command/DebugHintsCommand.php` | Placeholder CLI tool for template hints or layout exports |
| `Logger/*` | Custom Monolog logger for writing to `debug_order_email.log` |

---

## Setup Instructions

1. Place the module in your Magento 2 `app/code/Networld/Debug` directory (or load via composer).
2. Run:
   ```bash
   bin/magento module:enable Networld_Debug
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:flush
   ```
3. Check logs in:
   - `var/log/debug_order_email.log`
   - `var/log/system.log`

---

## Example Output

From `TransportTemplateVarsLogger`:

```json
{
  "template_var_keys": ["order", "delivery_date", "store"],
  "order_id": "100000123",
  "grand_total": 49.99,
  "shipping_method": "Flat Rate - Fixed",
  "delivery_date": "2025-07-23"
}
```

From `CaptureFinalEmail`:

```json
{
  "subject": "Your order confirmation",
  "to": ["customer@example.com"]
}
```

---

## Notes

- All logging is wrapped in try/catch and will not interfere with core functionality.
- No reflection or private property access is used — plugins are Magento- and PHP 8-safe.
- ASCII art banner added for flair ✨

---

© Networld Sports — Debug Module for Internal Use and Open Review.
