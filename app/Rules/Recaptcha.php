<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Skip validation if no secret key is configured (for development)
        if (!config('services.recaptcha.secret_key')) {
            return true;
        }

        // Skip validation on localhost/127.0.0.1 (development environment)
        $host = request()->getHost();
        $appUrl = config('app.url', '');
        if (in_array($host, ['localhost', '127.0.0.1']) || 
            str_contains($appUrl, 'localhost') || 
            str_contains($appUrl, '127.0.0.1') ||
            config('app.env') === 'local') {
            \Log::info('reCAPTCHA validation skipped on localhost/development environment');
            return true;
        }

        // If no value provided, fail validation
        if (empty($value)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            if (!$response->successful()) {
                \Log::warning('reCAPTCHA API returned non-200 status: ' . $response->status());
                // In production, fail validation if API call fails
                return config('app.env') === 'local' ? true : false;
            }

            $result = $response->json();

            // Check if the response was successful
            if (!isset($result['success']) || $result['success'] !== true) {
                $errorCodes = $result['error-codes'] ?? [];
                
                // Check for domain-related errors (localhost not in allowed domains)
                if (in_array('invalid-input-response', $errorCodes) || 
                    in_array('bad-request', $errorCodes)) {
                    // If on localhost and domain error, allow to pass (development)
                    $host = request()->getHost();
                    if (in_array($host, ['localhost', '127.0.0.1']) || config('app.env') === 'local') {
                        \Log::info('reCAPTCHA domain error on localhost - allowing to pass for development');
                        return true;
                    }
                }
                
                \Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $errorCodes,
                    'ip' => request()->ip(),
                    'host' => request()->getHost(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('reCAPTCHA verification exception: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'ip' => request()->ip(),
            ]);
            
            // In development, allow to pass; in production, fail for security
            return config('app.env') === 'local' ? true : false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Please confirm you are not a robot.');
    }
}
