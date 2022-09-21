<?php

namespace Tests;

use Config;
use EnvSecurity;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;

class EditTest extends TestCase
{
    use HandlesEnvFiles;

    /**
     * Need a specific SP to load a specific artisan command test double.
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProviderDouble::class];
    }

    public function testEditEmptyFile()
    {
        // Make sure no file is present
        if (file_exists($this->getFilePathForEnvironment('testing'))) {
            unlink($this->getFilePathForEnvironment('testing'));
        }

        // Setup a driver that fails if we call the encrypt method.
        EnvSecurity::extend('failonencrypt', function () {
            return new class {
                public function encrypt($plaintext)
                {
                    throw new \Exception('Should not be here. I received: '.$plaintext);
                }
            };
        });
        Config::set('env-security.default', 'failonencrypt');

        // Our test double will output the plaintext
        $this->artisan('env:edit testing')
            ->expectsOutput('Plaintext contents: ');

        // File should be empty
        $this->assertEquals('', $this->loadEncrypted('testing'));
    }

    public function testEditEncryptedFile()
    {
        // Setup a testing.env.enc file
        $this->saveEncrypted(EnvSecurity::encrypt('hello world'), 'testing');

        // Our test double will output the plaintext
        $this->artisan('env:edit testing --append modified')
            ->expectsOutput('Plaintext contents: hello world');

        // File will have "modified" appended and be re-encrypted
        $this->assertEquals(EnvSecurity::encrypt('hello world modified'), $this->loadEncrypted('testing'));
    }
}
