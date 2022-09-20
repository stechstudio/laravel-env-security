<?php

namespace Tests;

use Illuminate\Support\Facades\Config;
use STS\EnvSecurity\Drivers\GoogleKmsDriver;
use STS\EnvSecurity\Drivers\KmsDriver;
use STS\EnvSecurity\EnvSecurityFacade as EnvSecurity;

class ManagerTest extends TestCase
{
    public function testDefaultDriver()
    {
        Config::set('env-security.default', 'kms');
        $this->assertInstanceOf(KmsDriver::class, EnvSecurity::driver());

        Config::set('env-security.default', 'google_kms');
        Config::set('env-security.drivers.google_kms', [
            'project' => 'project-131708',
            'location' => 'global',
            'key_ring' => 'laravel',
            'key_id' => 'dotenv',
        ]);

        // We have to at least pretend to have valid Google credentials
        file_put_contents(__DIR__.'/store/keyfile.json', json_encode([
            'type' => 'service_account',
            'project_id' => '',
            'private_key_id' => '',
            'private_key' => '',
            'client_email' => '',
            'client_id' => '',
            'auth_uri' => '',
            'token_uri' => '',
            'auth_provider_x509_cert_url' => '',
            'client_x509_cert_url' => ''
        ]));
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.__DIR__.'/store/keyfile.json');

        $this->assertInstanceOf(GoogleKmsDriver::class, EnvSecurity::driver());

        Config::set('env-security.default', 'invalid');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [invalid] not supported');
        EnvSecurity::driver();
    }

    public function testResolveEnvironment()
    {
        // By default it will use our APP_ENV
        $this->assertEquals('testing', EnvSecurity::resolveEnvironment());

        EnvSecurity::setEnvironment(null);
        EnvSecurity::resolveEnvironmentUsing(function () {
            return "heya";
        });

        $this->assertEquals('heya', EnvSecurity::resolveEnvironment());

        EnvSecurity::setEnvironment("override");

        $this->assertEquals('override', EnvSecurity::resolveEnvironment());
    }

    public function testResolveKey()
    {
        // By default it will be null
        $this->assertNull(EnvSecurity::resolveKey());

        EnvSecurity::resolveKeyUsing(function ($environment) {
            return "alias/myapp-$environment";
        });

        $this->assertEquals("alias/myapp-testing", EnvSecurity::resolveKey());

        $this->assertEquals("alias/myapp-newenv", EnvSecurity::setEnvironment('newenv')->resolveKey());
    }
}
