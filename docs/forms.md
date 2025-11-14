# Forms & Services

This project handles forms via classic POST and Alpine.js (AJAX) with a service layer and safe email delivery.

## Contact Form
- View: `resources/views/pages/contact.blade.php`
- POST: `POST /contact` (name, email, phone?, subject, message, hp, recaptcha_token?)
- Controller: `App/Http/Controllers/ContactController@store`
  - Validates input; uses `ContactService` to create a row
  - Sends emails via `EmailService` in try/catch (non‑blocking)
- Model: `App/Models/Contact`
- Table: `contacts`

## Reseller Application
- View: `resources/views/components/reseller-section.blade.php`
- POST: `POST /reseller` (name, email, phone, company?, investmentCapacity, message?, hp, recaptcha_token?)
- Controller: `App/Http/Controllers/ResellerController@store`
  - Validates input; uses `ResellerService` to create a row
  - Sends emails via `EmailService` in try/catch (non‑blocking)
- Model: `App/Models/ResellerApplication`
- Table: `reseller_applications`

## Newsletter Signup
- View: `resources/views/components/signup-section.blade.php`
- POST: `POST /newsletter` (email, whatsapp?, hp)
- Controller: `App/Http/Controllers/NewsletterController@store`
  - Validates input; upserts subscriber via `NewsletterService`
  - Sends welcome email via `EmailService` in try/catch (non‑blocking)
- Model: `App/Models/NewsletterSubscription`
- Table: `newsletter_subscriptions`

## Anti‑Spam & Abuse Protections
- Honeypot `hp` across forms; server validates `max:0`
- Throttling (see `routes/web.php`):
  - Contact/Reseller: 3/hour, Newsletter: 6/hour
  - Login: 5/min
- Optional reCAPTCHA v3 on Contact + Reseller when keys present in `config/services.php`

## Service Layer
- `App/Services/ContactService.php` → creates contacts, logs actions
- `App/Services/ResellerService.php` → creates applications, logs actions
- `App/Services/NewsletterService.php` → upserts subscriptions, logs actions
- `App/Services/EmailService.php` → sends notifications (admin/user) and newsletter

## Routes (extract)
```php
// routes/web.php
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,60')->name('contact.store');
Route::post('/reseller', [ResellerController::class, 'store'])->middleware('throttle:3,60')->name('reseller.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:6,60')->name('newsletter.store');
```

## Controller Snippet (Reseller)
```php
// App/Http/Controllers/ResellerController.php (extract)
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|max:255',
    'phone' => 'required|string|max:20',
    'company' => 'nullable|string|max:255',
    'investmentCapacity' => 'required|string|in:1-10k,10-50k,50-100k,100k+',
    'message' => 'nullable|string|max:1000',
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

$reseller = $this->resellerService->createApplication($validated);
```

## Flash Messages & Errors
- Classic POST forms return with session flashes on success and with validation errors on failure.
- Alpine forms set `success`/`error` state flags and reset fields as appropriate.
