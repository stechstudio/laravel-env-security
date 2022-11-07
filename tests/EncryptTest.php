<?php

namespace Tests;

use Config;
use EnvSecurity;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;

class EncryptTest extends TestCase
{
    use HandlesEnvFiles;

    public function testEncrypt()
    {
        @unlink($this->getFilePathForEnvironment('testing'));
        file_put_contents(base_path('.env'), "encrypt=this");

        $this->artisan('env:store testing')
            ->expectsOutput('Saved the contents of your current .env file for environment [testing]');

        $this->assertTrue(file_exists($this->getFilePathForEnvironment('testing')));
        $this->assertEquals('encrypt=this', EnvSecurity::decrypt($this->loadEncrypted("testing")));
    }

    public function testEncryptMissingFile()
    {
        // Make sure no file is present
        @unlink(base_path('.env'));

        $this->artisan('env:store testing')
            ->expectsOutput('Make sure you have a .env file in your base project path');
    }
}
