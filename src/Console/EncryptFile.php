<?php

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class EncryptFile extends Command
{
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
        KMSDotEnv::encryptFile($this->argument('in'))
            ->saveEncryptedFile($this->argument('out'));
        $this->info(sprintf('%s encrypted to %s.', $this->argument('in'), $this->argument('out')));
    }
}
