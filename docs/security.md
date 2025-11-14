# Security Hardening

This project ships with multiple layers of protection without hurting UX.

## Headers (Middleware)
- Middleware: `App/Http/Middleware/SecurityHeaders.php`
- Sets:
  - `Content-Security-Policy` (self by default; Google Fonts + Tag Manager allowed)
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: DENY`
  - `Referrer-Policy: no-referrer-when-downgrade`
  - `Permissions-Policy: geolocation=(), camera=(), microphone=()`
- Enabled globally in `App/Http/Kernel.php`.

Tip: If you add third‑party scripts/assets, extend CSP accordingly.

## CSRF
- CSRF is enforced globally; `VerifyCsrfToken` has no exclusions.
- Forms include `@csrf`. AJAX includes `X-CSRF-TOKEN` from meta tag.

## Throttling
- Login: `throttle:5,1` (5/min per email+IP)
- Contact/Reseller: `throttle:3,60` (3/hour)
- Newsletter: `throttle:6,60` (6/hour)
- Applied to both classic POST and `/api` prefixes in `routes/web.php`.

## Honeypots
- Hidden field `hp` added to reseller, newsletter, and contact forms.
- Server validation enforces `max:0`.
- Client checks prevent obvious bot submissions.

## reCAPTCHA v3 (Optional)
- Config: `config/services.php` → `recaptcha.site_key`, `secret_key`, `min_score`.
- Frontend injects `recaptcha_token` (Contact: Blade form; Reseller: Alpine submit).
- Controllers verify token server‑side and soft‑fail if score < min.

## Validation & Non‑Blocking Emails
- All controllers validate inputs strictly.
- Emails are sent in try/catch; failures are logged and do not block success responses.

## Admin 2FA Enforcement
- Fortify installed; `TwoFactorAuthenticatable` on `User`.
- Middleware: `admin.2fa` redirects admins without 2FA enabled to `/admin/2fa/setup`.
- Challenge page used on login.

## Production `.env`
```
APP_ENV=production
APP_DEBUG=false
```

Keep dependencies up to date and monitor logs in `storage/logs/laravel.log`.

---

## Snippets

### CSP & Security Headers Middleware
```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $csp = "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; "
             . "img-src 'self' data: https:; "
             . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com data:; "
             . "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com; "
             . "connect-src 'self';";
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', "geolocation=(), camera=(), microphone=()");
        $response->headers->set('Content-Security-Policy', $csp);
        return $response;
    }
}
```

### Register Middleware Globally
```php
// app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecurityHeaders::class,
];

protected $middlewareAliases = [
    // ...
    'role' => \App\Http\Middleware\RoleMiddleware::class,
    'admin.2fa' => \App\Http\Middleware\EnsureAdminTwoFactorEnabled::class,
];
```

### Throttled Routes
```php
// routes/web.php (extract)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,60')->name('contact.store');
Route::post('/reseller', [ResellerController::class, 'store'])->middleware('throttle:3,60')->name('reseller.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:6,60')->name('newsletter.store');

Route::prefix('api')->group(function () {
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,60');
    Route::post('/reseller', [ResellerController::class, 'store'])->middleware('throttle:3,60');
    Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:6,60');
});
```

### Optional reCAPTCHA Verification
```php
// ContactController@store (extract)
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255',
    'phone' => 'nullable|string|max:20',
    'message' => 'required|string|max:1000',
    'hp' => 'nullable|string|max:0',
    'recaptcha_token' => 'nullable|string',
]);

if (config('services.recaptcha.secret_key') && ! empty($validated['recaptcha_token'])) {
    $resp = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret' => config('services.recaptcha.secret_key'),
        'response' => $validated['recaptcha_token'],
        'remoteip' => $request->ip(),
    ])->json();
    if (!($resp['success'] ?? false) || (($resp['score'] ?? 0) < (float) config('services.recaptcha.min_score'))) {
        return back()->withErrors(['message' => 'Unable to verify your submission. Please try again.']);
    }
}
```
