# Tawk.to Live Chat Integration

This document describes the tawk.to live chat widget integration for the RWAMP Laravel application.

## Overview

The tawk.to live chat widget has been integrated into the application to provide customer support functionality. The integration is environment-driven, secure, and CSP-compliant.

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Tawk.to Live Chat Widget Configuration
# Enable/disable the tawk.to widget (default: true)
TAWK_TO_ENABLED=true

# Tawk.to Widget ID (format: {property_id}/{widget_id})
# Example: 691ec32b545b891960a7807b/1jag2kp6s
TAWK_TO_WIDGET_ID=691ec32b545b891960a7807b/1jag2kp6s
```

### Configuration File

The tawk.to configuration is stored in `config/services.php`:

```php
'tawk' => [
    'enabled' => env('TAWK_TO_ENABLED', true),
    'widget_id' => env('TAWK_TO_WIDGET_ID'),
],
```

## Files Modified

### 1. `config/services.php`
- Added tawk.to configuration section

### 2. `resources/views/components/tawk-to.blade.php` (NEW)
- Blade partial component for the tawk.to widget
- Conditionally loads only if enabled and widget_id is configured
- Automatically sets user attributes for authenticated users (name, email, role, user_id)

### 3. `resources/views/layouts/app.blade.php`
- Added `@include('components.tawk-to')` just before `</body>` tag
- Ensures widget loads asynchronously without blocking page rendering

### 4. `app/Http/Middleware/SecurityHeaders.php`
- Updated Content Security Policy (CSP) to allow:
  - `script-src`: `https://embed.tawk.to` and `https://*.tawk.to`
  - `connect-src`: `https://embed.tawk.to` and `https://*.tawk.to`
  - `frame-src`: `https://*.tawk.to`
  - `img-src`: `https://*.tawk.to` and `data:`

## Features

### User Identity Binding

For authenticated users, the widget automatically sets the following attributes:
- **Name**: User's full name
- **Email**: User's email address
- **Role**: User's role (admin, reseller, investor)
- **User ID**: User's database ID

This allows support agents to see who they're chatting with and access relevant user information.

### Conditional Loading

The widget only loads if:
1. `TAWK_TO_ENABLED` is set to `true` (or not set, defaults to true)
2. `TAWK_TO_WIDGET_ID` is configured

This allows you to disable the widget in specific environments (e.g., local development) by setting `TAWK_TO_ENABLED=false` in your `.env` file.

### Security

- **CSP Compliant**: All tawk.to domains are explicitly allowed in the Content Security Policy
- **No Hardcoded Secrets**: Widget ID is stored in environment variables
- **Async Loading**: Script loads asynchronously to prevent blocking page rendering
- **Cross-Origin**: Properly configured with `crossorigin='*'` attribute

## Usage

### Enable/Disable Widget

To disable the widget, set in `.env`:
```env
TAWK_TO_ENABLED=false
```

To enable it again:
```env
TAWK_TO_ENABLED=true
```

### Change Widget ID

Update the widget ID in `.env`:
```env
TAWK_TO_WIDGET_ID=your_property_id/your_widget_id
```

After changing environment variables, clear the configuration cache:
```bash
php artisan config:clear
```

## Testing

1. **Verify Widget Loads**: Check browser console for any CSP violations
2. **Test User Identity**: Log in as a user and verify that the chat widget shows the correct user information
3. **Test Disable**: Set `TAWK_TO_ENABLED=false` and verify the widget doesn't load
4. **Test CSP**: Check browser console for any Content Security Policy errors

## Troubleshooting

### Widget Not Loading

1. Check that `TAWK_TO_ENABLED=true` in `.env`
2. Verify `TAWK_TO_WIDGET_ID` is correctly set
3. Clear config cache: `php artisan config:clear`
4. Check browser console for CSP violations

### CSP Violations

If you see CSP violations in the browser console:
1. Verify `app/Http/Middleware/SecurityHeaders.php` includes tawk.to domains
2. Clear application cache: `php artisan cache:clear`
3. Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)

### User Identity Not Showing

1. Ensure user is authenticated (logged in)
2. Check browser console for JavaScript errors
3. Verify `Tawk_API.onLoad` callback is executing

## Notes

- The widget loads on all pages (public and authenticated)
- User identity is only set for authenticated users
- The widget script is loaded asynchronously to maintain page performance
- All tawk.to domains are whitelisted in CSP for security compliance

---

**Last Updated**: December 2025
**Status**: Production Ready

