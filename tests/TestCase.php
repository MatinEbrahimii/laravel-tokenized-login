<?php

namespace MatinEbrahimi\TokenizedLogin;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [\MatinEbrahimi\TokenizedLogin\TwoFactorAuthServiceProvider::class];
    }
}
