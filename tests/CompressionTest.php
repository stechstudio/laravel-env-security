<?php

namespace Tests;

use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;
use STS\EnvSecurity\EnvSecurityFacade;

class CompressionTest extends TestCase
{
    use HandlesEnvFiles;

    public function testEncryptCompressedData()
    {
        $environment_1 = 'testing_uncompressed';
        $environment_2 = 'testing_compressed';

        // Random blob of text, but big enough for compression to be noticeable
        $plaintext = 'blob_o_text='.base64_encode(random_bytes(100000));
        @unlink($this->getFilePathForEnvironment($environment_1));
        @unlink($this->getFilePathForEnvironment($environment_2));

        file_put_contents(base_path('.env'), $plaintext);

        // Encrypt without compression
        $this->artisan("sts-env:encrypt {$environment_1}")
            ->expectsOutput("Saved the contents of your current .env file for environment [{$environment_1}]");
        $this->assertTrue(file_exists($this->getFilePathForEnvironment($environment_1)));

        // Encrypt the same with compression
        $this->artisan("sts-env:encrypt {$environment_2} --compress")
            ->expectsOutput("Saved the contents of your current .env file for environment [{$environment_2}]");
        $this->assertTrue(file_exists($this->getFilePathForEnvironment($environment_2)));

        // Verify that the uncompressed file is larger than the compressed file.
        $this->assertTrue(filesize($this->getFilePathForEnvironment($environment_1)) > filesize($this->getFilePathForEnvironment($environment_2)));

        // Verify the decrypted uncompressed file is equal to the expected plain text.
        $this->assertEquals($plaintext, EnvSecurityFacade::decrypt($this->loadEncrypted($environment_1)));
        // Verify that the decrypted compressed file is equal to the decrypted uncompressed filesss
        $this->assertEquals(EnvSecurityFacade::decrypt($this->loadEncrypted($environment_1)),
            EnvSecurityFacade::decrypt($this->loadEncrypted($environment_2)));
    }

}
