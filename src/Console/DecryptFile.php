<?php

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Crypto\Client;

class DecryptFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:decrypt-file
                            {in : Path to the file to decrypt.} 
                            {out : Path to store the plaintext file. } 
                            {region=us-east-1 : AWS Region the key is in.}
                            {--K|kmsid : KMS Key ID or Alias}';

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
        (new Client(
            is_null($this->option('kmsid')) ?
                config('kms.keyId') :
                $this->option('kmsid'),
            ['region' => $this->argument('region')]
        ))
            ->decryptFile($this->argument('in'))
            ->saveDecryptedFile($this->argument('out'));
        $this->info(sprintf('%s decrypted to %s.', $this->argument('in'), $this->argument('out')));
    }
}
