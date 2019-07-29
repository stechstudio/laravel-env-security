<?php

namespace Tests;

use Config;
use EnvSecurity;
use Orchestra\Testbench\TestCase as BaseTestCase;
use STS\EnvSecurity\EnvSecurityFacade;
use STS\EnvSecurity\EnvSecurityServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [EnvSecurityServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'EnvSecurity' => EnvSecurityFacade::class
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        @mkdir(__DIR__ . '/store');

        EnvSecurity::extend('laravel', function() {
            return app('encrypter');
        });

        EnvSecurity::extend('test', function() {
            return new class {
                public function encrypt($plaintext) {
                    return base64_encode($plaintext);
                }

                public function decrypt($ciphertext) {
                    return base64_decode($ciphertext);
                }
            };
        });

        Config::set('env-security.default', 'test');
        Config::set('env-security.store', __DIR__ . '/store');
        Config::set('env-security.destination', __DIR__ . '/.env-saved');
    }
}