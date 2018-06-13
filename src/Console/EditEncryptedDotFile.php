<?php

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class EditEncryptDotFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:edit {environment : The environment file to decrypt.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit an encrypted dotfile.';

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

        $encryptedFilename = sprintf(config('kms.ciphertext_filename_template'), $this->argument('environment'));
        $source = sprintf('%s/%s', config('kms.dir_ciphertext'), $encryptedFilename);

        KMSDotEnv::editEncryptedFile(
            config('kms.editor'),
            $source
        );

        $this->info(sprintf('Successfully updated %s.', $source));
    }
}
