<?php

namespace Tests;

use Config;
use EnvSecurity;
use STS\EnvSecurity\Drivers\KmsDriver;

class ManagerTest extends TestCase
{
    public function testDefaultDriver()
    {
        Config::set('env-security.default', 'kms');
        $this->assertInstanceOf(KmsDriver::class, EnvSecurity::driver());

        Config::set('env-security.default', 'invalid');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [invalid] not supported');
        EnvSecurity::driver();
    }

    public function testResolveEnvironment()
    {
        // By default it will use our APP_ENV
        $this->assertEquals('testing', EnvSecurity::resolveEnvironment());

        EnvSecurity::resolveEnvironmentUsing(function() {
            return "heya";
        });

        $this->assertEquals('heya', EnvSecurity::resolveEnvironment());
    }
}