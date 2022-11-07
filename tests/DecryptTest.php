<?php

namespace Tests;

use Config;
use EnvSecurity;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;

class DecryptTest extends TestCase
{
    use HandlesEnvFiles;

    /**
     * Need a specific SP to load a specific artisan command test double
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProviderDouble::class];
    }

    public function testDecrypt()
    {
        $this->saveEncrypted(EnvSecurity::encrypt('hello world'), 'testing');

        $this->artisan('env:fetch testing')
            ->expectsOutput('Successfully decrypted .env for environment [testing]');

        $this->assertTrue(file_exists(__DIR__ . "/.env-saved"));
        $this->assertEquals('hello world', file_get_contents(__DIR__ . "/.env-saved"));
    }

    public function testDecryptMissingFile()
    {
        // Make sure no file is present
        if(file_exists($this->getFilePathForEnvironment('testing'))) {
            unlink($this->getFilePathForEnvironment('testing'));
        }

        // Our test double will output the plaintext
        $this->artisan('env:fetch testing')
            ->expectsOutput('Unable to load encrypted .env file for environment [testing]');
    }

    public function testDecryptResolveEnvironment()
    {
        EnvSecurity::resolveEnvironmentUsing(function() {
            return 'foobar';
        });

        $this->saveEncrypted(EnvSecurity::encrypt('heya'), 'foobar');

        $this->artisan('env:fetch')
            ->expectsOutput('Successfully decrypted .env for environment [foobar]');

        $this->assertTrue(file_exists(__DIR__ . "/.env-saved"));
        $this->assertEquals('heya', file_get_contents(__DIR__ . "/.env-saved"));
    }

    public function testDecryptResolveKey()
    {
        // This is the
        EnvSecurity::resolveEnvironmentUsing(function() {
            return 'testing';
        });

        EnvSecurity::resolveKeyUsing(function($environment) {
            return 'mykey-' . $environment;
        });

        $this->saveEncrypted(EnvSecurity::encrypt('heya'), 'testing');
        $this->saveEncrypted(EnvSecurity::encrypt('this is a separate environment file'), 'altenv');

        $this->artisan('env:fetch')
            ->expectsOutput('Used key [mykey-testing]');

        // Now specify alternate environment from CLI, ensure we use that environment's key regardless of
        // our previously provided resolver
        $this->artisan('env:fetch altenv')
            ->expectsOutput('Used key [mykey-altenv]');

        $this->assertEquals('this is a separate environment file', file_get_contents(__DIR__ . "/.env-saved"));
    }
}
