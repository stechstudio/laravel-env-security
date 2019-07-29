<?php

namespace Tests;

use Config;
use EnvSecurity;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;

class DecryptTest extends TestCase
{
    use HandlesEnvFiles;

    public function testDecrypt()
    {
        $this->saveEncrypted(EnvSecurity::encrypt('hello world'), 'testing');

        $this->artisan('env:decrypt testing')
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
        $this->artisan('env:decrypt testing')
            ->expectsOutput('Unable to load encrypted .env file for environment [testing]');
    }

    public function testDecryptResolveEnvironment()
    {
        EnvSecurity::resolveEnvironmentUsing(function() {
            return 'foobar';
        });

        $this->saveEncrypted(EnvSecurity::encrypt('heya'), 'foobar');

        $this->artisan('env:decrypt')
            ->expectsOutput('Successfully decrypted .env for environment [foobar]');

        $this->assertTrue(file_exists(__DIR__ . "/.env-saved"));
        $this->assertEquals('heya', file_get_contents(__DIR__ . "/.env-saved"));
    }
}