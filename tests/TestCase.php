<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Http\Middleware\EnsureAdminTwoFactorEnabled;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable admin 2FA middleware in tests so admin routes are directly testable
        $this->withoutMiddleware(EnsureAdminTwoFactorEnabled::class);
    }
}


