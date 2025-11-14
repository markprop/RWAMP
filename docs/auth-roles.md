# Auth Roles & Dashboards

This project implements role‑based dashboards and guarded access with optional 2FA enforcement for admins.

## Roles
- `investor`
- `reseller`
- `admin`

## Dashboards
- Investor: `/dashboard/investor`
- Reseller: `/dashboard/reseller`
- Admin: `/dashboard/admin` (requires role:admin and 2FA via `admin.2fa`)

## Redirects
- `AuthController@login` and `register` redirect to the role‑specific dashboard via `redirectByRole`.
- Navbar shows a role‑aware “Dashboard” link and an admin 2FA status badge.

## Middleware
- `role` middleware checks the user’s role for the dashboard routes.
- `admin.2fa` middleware redirects admins without 2FA enabled to `/admin/2fa/setup`.

## Admin Dashboard
- Metrics: user counts, new users 7/30d, contacts, reseller apps breakdown, coin price placeholder.
- Approvals: approve/reject reseller applications with CSRF‑protected PUT forms.

## 2FA Setup (Admin)
- `/admin/2fa/setup` → enable, QR, recovery codes, regenerate, disable.
- Fortify challenge triggered on login when required.

## SEO on Public Pages
- Titles/descriptions/meta are passed from controllers/routes and rendered in `layouts/app.blade.php`.

---

## Snippets

### Role‑based Dashboards (Routes)
```php
// routes/web.php (extract)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/investor', fn () => view('dashboard.investor'))
        ->middleware('role:investor')->name('dashboard.investor');
    Route::get('/dashboard/reseller', fn () => view('dashboard.reseller'))
        ->middleware('role:reseller')->name('dashboard.reseller');
    Route::get('/dashboard/admin', [AdminController::class, 'dashboard'])
        ->middleware(['role:admin','admin.2fa'])->name('dashboard.admin');
});
```

### Login Redirect by Role
```php
// App/Http/Controllers/AuthController.php (extract)
private function redirectByRole(User $user, ?string $intendedRole = null)
{
    $role = $intendedRole ?? $user->role;
    return match ($role) {
        'admin' => redirect()->route('dashboard.admin'),
        'reseller' => redirect()->route('dashboard.reseller'),
        default => redirect()->route('dashboard.investor'),
    };
}
```

### Admin 2FA Enforcement
```php
// app/Http/Middleware/EnsureAdminTwoFactorEnabled.php (extract)
if ($user && $user->role === 'admin') {
    if (empty($user->two_factor_secret)) {
        if (! $request->routeIs('admin.2fa.setup')) {
            return redirect()->route('admin.2fa.setup');
        }
    }
}
```
