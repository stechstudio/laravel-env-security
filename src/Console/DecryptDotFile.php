<?php

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class DecryptDotFile extends Command
{
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
        if (empty(config('kms.kms_key_id'))) {
            $this->error('You need to configure kmsdotenv.');

            return;
        }

        $source = sprintf('%s/%s', config('kms.dir_ciphertext'), $this->getCiphertextFile());

        if (! is_file($source)) {
            $this->error(sprintf('%s does not exist.', $source));

            return 1;
        }

        KMSDotEnv::decryptFile($source)
            ->saveDecryptedFile(config('kms.file_plaintext'));

        $this->report();
    }

    protected function report(): void
    {
        $this->info(
            sprintf(
                '%s/%s encrypted to %s.',
                config('kms. dir_ciphertext'),
                config('kms.file_ciphertext'),
                config('kms.file_plaintext')
            )
        );
    }

    /**
     * @return string
     */
    protected function getCiphertextFile(): string
    {
        return empty($this->option('environment')) ?
            config('kms.file_ciphertext') :
            sprintf('%s.env.enc', $this->option('environment'));
    }
}
