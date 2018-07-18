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
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class EncryptFile extends Command
{
    use KeyConfiguration, RegionConfiguration;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:encrypt-file 
                            {in : Path to the file to encrypt.} 
                            {out : Path to store the encrypted file. } 
                            {region=us-east-1 : AWS Region the key is in.}
                            {--K|kmsid : KMS Key ID or Alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt a file.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->configureKey();
            KMSDotEnv::encryptFile($this->argument('in'), $this->configureRegion())
                ->saveEncryptedFile($this->argument('out'), $this->configureRegion());
        } catch (ConfigurationException $e) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        $this->info(sprintf('%s encrypted to %s.', $this->argument('in'), $this->argument('out')));
    }
}
