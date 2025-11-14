# Admin Two‑Factor Authentication (2FA)

This project uses Laravel Fortify for TOTP‑based 2FA (e.g., Google Authenticator) and enforces 2FA for Admin dashboard access.

## What’s Implemented
- Fortify installed and configured, migrations applied
- `User` model uses `TwoFactorAuthenticatable`
- Admin‑only enforcement via middleware `admin.2fa`
- Setup UI at `/admin/2fa/setup` (enable, QR, recovery codes, regenerate, disable)
- Challenge view for code entry on login
- Navbar badge for admin 2FA status

## Enabling 2FA (Admin)
1. Log in as `role=admin`.
2. Visit `/admin/2fa/setup`.
3. Click “Enable Two‑Factor Authentication”.
4. Scan the QR code using an authenticator app.
5. Store your recovery codes securely.

Once enabled, access to `/dashboard/admin` is allowed. If disabled or never enabled, admins are redirected to setup.

## Fortify Routes (used internally)
- POST `user/two-factor-authentication` → enable 2FA
- DELETE `user/two-factor-authentication` → disable 2FA
- POST `user/two-factor-recovery-codes` → regenerate recovery codes
- POST `/two-factor-challenge` → verify code (on login)

## Config & Views
- Provider: `app/Providers/FortifyServiceProvider.php`
- Views:
  - `resources/views/auth/two-factor-setup.blade.php`
  - `resources/views/auth/two-factor-challenge.blade.php`
  - `resources/views/auth/confirm-password.blade.php`

## Tips
- In production `.env`, set `APP_ENV=production`, `APP_DEBUG=false`.
- Consider emailing admins when 2FA is enabled/disabled (event listener).
