<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Console\Concerns\KeyConfiguration;
use STS\Kms\DotEnv\Console\Concerns\RegionConfiguration;
use STS\Kms\DotEnv\Console\Concerns\SourceConfiguration;
use STS\Kms\DotEnv\Exceptions\ConfigurationException;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class EncryptDotFile extends Command
{
    use KeyConfiguration, RegionConfiguration, SourceConfiguration;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:encrypt-dotfile
                            {--i|in : Path to the file to encrypt.} 
                            {--o|out : Path to store the ciphertext file. } 
                            {--r|region : AWS Region the key is in.}
                            {--k|kmsid : KMS Key ID or Alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt a dotfile.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->configureKey();

            KMSDotEnv::encryptFile($this->getPath('in', config('kms.file_plaintext')), $this->configureRegion())
                ->saveEncryptedFile($this->getPath('out', config('kms.path_ciphertext')), $this->configureRegion());
        } catch (ConfigurationException $e) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        $this->info(sprintf(
            'Successfully encrypted %s to %s.',
            $this->getPath('in', config('kms.file_plaintext')),
            $this->getPath('out', config('kms.path_ciphertext'))
        ));

        return 0;
    }
}
