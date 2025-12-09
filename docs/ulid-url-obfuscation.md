## ULID-based URL Obfuscation – Implementation Notes

### Overview

This project introduces **ULID-based public identifiers** for all core entities that are exposed via URLs (users, crypto payments, withdrawals, reseller applications, transactions). ULIDs are:

- Globally unique, 26-character lexicographically sortable strings.
- Non-sequential and hard to guess compared to auto-increment IDs.
- Backwards-compatible with existing numeric IDs and routes.

The change is intentionally conservative: **business logic, controllers, views, and middleware behavior remain the same**. Only identifiers and paths used in URLs are obfuscated.

### Schema & Models

1. **HasUlid trait**

- Located at `app/Concerns/HasUlid.php`.
- Responsibilities:
  - Adds a `creating` hook to auto-populate `ulid` when missing using `Str::ulid()`.
  - Overrides `getRouteKeyName()` so route model binding uses `ulid` instead of `id`.

2. **ULID columns**

- Migration: `database/migrations/2025_12_02_120000_add_ulid_columns_to_routed_tables.php`.
- Adds nullable, unique `ulid CHAR(26)` columns to:
  - `users`
  - `crypto_payments`
  - `withdraw_requests`
  - `reseller_applications`
  - `transactions`
  - Optional tables (only applied if they exist): `pages`, `posts`, `docs`, `projects`, `news`, `kyc_submissions`.
- Columns are nullable to allow a safe, incremental rollout. Existing records are backfilled via the Artisan command below.

3. **Models using HasUlid**

- `App\Models\User`
- `App\Models\CryptoPayment`
- `App\Models\WithdrawRequest`
- `App\Models\ResellerApplication`
- `App\Models\Transaction`

All new instances of these models automatically receive a unique ULID. Because `getRouteKeyName()` returns `ulid`, any route-model-bound parameters now expect ULIDs in the URL.

### Backfill Command

1. **Command**

- Class: `App\Console\Commands\BackfillUlids`.
- Signature: `php artisan ulid:backfill {--model=*}`
- By default, operates on:
  - `App\Models\User`
  - `App\Models\CryptoPayment`
  - `App\Models\WithdrawRequest`
  - `App\Models\ResellerApplication`
  - `App\Models\Transaction`

2. **Behavior**

- For each model:
  - Skips if the underlying table does not have a `ulid` column.
  - Counts records where `ulid` is `NULL`.
  - Iterates in chunks of 500, assigns `Str::ulid()` to each missing record, and saves quietly.
  - Shows a progress bar and per-model status in the console.

3. **Usage**

```bash
php artisan migrate
php artisan ulid:backfill

# Optional: limit to a single model
php artisan ulid:backfill --model=App\\Models\\User
```

### Routing Strategy

1. **Goal**

- Expose **short, ULID-based URLs** for admin resources while preserving existing route names and compatibility.
- Example patterns:
  - Admin users: `/a/u/{ulid}`
  - Admin crypto payments: `/a/p/{ulid}`
  - Admin withdrawals: `/a/w/{ulid}`
  - Admin applications: `/a/ap/{ulid}`

2. **Key changes in `routes/web.php`**

- **Admin User Management**
  - Short URLs:
    - Group prefix: `a/u`
    - Route names preserved: `admin.users.*`
    - Bound parameter `{user}` now expects a ULID (via route model binding).
  - Legacy numeric routes:
    - `/dashboard/admin/users/{id}` (numeric) now resolves the user by `id` and redirects to `/a/u/{user.ulid}` / `route('admin.users.show', $user)`.
  - Index route:
    - `/dashboard/admin/users` now permanently redirects to `route('admin.users')`, whose path can later be adjusted to `/a/u` if desired.

- **Admin Crypto Payments**
  - Group prefix changed from `dashboard/admin/crypto-payments` to `a/p` for detail/update/approve/reject routes.
  - Route names (`admin.crypto.payments.*`) remain the same.
  - Legacy index route `/dashboard/admin/crypto-payments` redirects to `route('admin.crypto.payments')`.

- **Admin Applications (Reseller Applications)**
  - Group prefix changed to `a/ap`.
  - Route names (`admin.applications.*`) are unchanged.

- **Admin Withdrawals**
  - Group prefix changed to `a/w`.
  - Route names (`admin.withdrawals.*`) are unchanged.
  - Legacy index route `/dashboard/admin/withdrawals` redirects to `route('admin.withdrawals')`.

3. **Backward compatibility**

- Because `getRouteKeyName()` uses `ulid`, any route using implicit model binding with parameters like `{user}`, `{payment}`, `{withdrawal}`, `{application}` will now bind by ULID without needing controller changes.
- Numeric ID-based legacy URLs are preserved only where necessary and converted via redirects that look up the correct model and redirect to its ULID URL.

### Controllers & Business Logic

- No controller methods were modified for business logic.
- Model resolution is handled via **route model binding** using the ULID key, courtesy of the `HasUlid` trait.
- Any controllers that previously type-hinted models in route parameters (e.g. `show(User $user)`) will now receive the correct model instance when given a ULID in the URL.

### Blade Views

- With `HasUlid` enabled, recommended usage is:

```php
route('admin.users.show', $user->ulid)
// or, when the route uses implicit model binding:
route('admin.users.show', $user)
```

- The second form (`$user`) is preferred because it is resilient to future changes to the route key name (still uses ULID under the hood).

### Rollout Checklist

1. Deploy migration and ULID trait.
2. Run:

```bash
php artisan migrate
php artisan ulid:backfill
```

3. Verify:
   - All relevant tables have a non-null, unique `ulid` for existing records.
   - Admin panels load correctly using the new short URLs (`/a/u/...`, `/a/p/...`, `/a/w/...`, `/a/ap/...`).
4. Gradually update Blade views to:
   - Use `route('name', $model)` or `route('name', $model->ulid)` for any routes that take modeled parameters.
5. Monitor logs for any 404s/route-model-binding errors and adjust legacy redirects if needed.

### Notes / Future Work

- The current implementation focuses on **admin-facing resources** (`users`, `crypto_payments`, `withdrawals`, `reseller_applications`, `transactions`).
- Public pages (`/about`, `/contact`, etc.) can be migrated to `/p/{ulid}` patterns by introducing a `Page` model and using ULID-based routing in a similar manner, but that would also require content management changes and SEO planning.
- If additional models are exposed via URLs later, you can:
  - Add a `ulid` column via a small migration.
  - Attach the `HasUlid` trait to the model.
  - Register the model class in `BackfillUlids::$knownModels` and rerun the backfill command.


