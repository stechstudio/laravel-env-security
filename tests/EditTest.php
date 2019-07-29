<?php

namespace Tests;

use Config;
use EnvSecurity;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;

class EditTest extends TestCase
{
    use HandlesEnvFiles;

    public function testEditEncryptedFile()
    {
        // Setup a testing.env.enc file
        $this->saveEncrypted(EnvSecurity::encrypt('hello world'), "testing");

        // Our test double will output the plaintext
        $this->artisan('env:edit testing --append modified')
            ->expectsOutput('Plaintext contents: hello world');

        // File will have "modified" appended and be re-encrypted
        $this->assertEquals(EnvSecurity::encrypt('hello world modified'), $this->loadEncrypted('testing'));
    }
}