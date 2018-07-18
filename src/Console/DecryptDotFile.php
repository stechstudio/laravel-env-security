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
use STS\Kms\DotEnv\Console\Concerns\SourceConfiguration;
use STS\Kms\DotEnv\Exceptions\ConfigurationException;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class DecryptDotFile extends Command
{
    use SourceConfiguration;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:decrypt-dotfile {--e|environment= : The environment file to decrypt.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypt a dotfile.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            KMSDotEnv::decryptFile($this->getCiphertextFileOption())->saveDecryptedFile(config('kms.file_plaintext'));
        } catch (ConfigurationException $e) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        $this->info(sprintf(
            'Successfully decrypted %s to %s.',
            $this->getCiphertextFileOption(),
            config('kms.file_plaintext')
        ));

        return 0;
    }
}
