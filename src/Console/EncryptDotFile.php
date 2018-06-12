<?php

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class EncryptDotFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:encrypt-dotfile';

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
        if (empty(config('kms.kms_key_id'))) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        if (! is_dir(config('kms.dir_ciphertext'))) {
            mkdir(config('kms.dir_ciphertext'));
        }
        KMSDotEnv::encryptFile(config('kms.file_plaintext'))
            ->saveEncryptedFile(sprintf('%s/%s', config('kms.dir_ciphertext'), config('kms.file_ciphertext')));

        $this->info(
            sprintf(
                '%s encrypted to %s/%s.',
                config('kms.file_plaintext'),
                config('kms.dir_ciphertext'),
                config('kms.file_ciphertext')
            )
        );
    }
}
