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

class DecryptFile extends Command
{
    use KeyConfiguration, RegionConfiguration;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:decrypt-file
                            {in : Path to the file to decrypt.} 
                            {out : Path to store the plaintext file. } 
                            {--r|region : AWS Region the key is in.}
                            {--k|kmsid : KMS Key ID or Alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypt a file.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->configureKey();
            KMSDotEnv::decryptFile($this->argument('in'), $this->configureRegion())
                ->saveDecryptedFile($this->argument('out'), $this->configureRegion());
        } catch (ConfigurationException $e) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        $this->info(sprintf('%s decrypted to %s.', $this->argument('in'), $this->argument('out')));
    }
}
