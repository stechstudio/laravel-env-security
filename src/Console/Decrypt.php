<?php
/*
 * This file is part of laravel-env-security.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\EnvSecurity\Console;

use Illuminate\Console\Command;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;
use STS\EnvSecurity\EnvSecurityManager;


/**
 * Class Decrypt
 * @package STS\EnvSecurity\Console
 */
class Decrypt extends Command
{
    use HandlesEnvFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:decrypt
                            {environment? : Which environment file you wish to decrypt}
                            {--o|out= : Saves the decrypted file to an alternate location}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypt a .env file. Tries to deduce the environment if none provided.';

    /**
     * @var EnvSecurityManager
     */
    protected EnvSecurityManager $envSecurity;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->envSecurity = $envSecurity;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->envSecurity->setEnvironment($this->environment());

        if (!$environment = $this->environment()) {
            $this->error("No environment specified, and we couldn't resolve it on our own");

            return 1;
        }

        if (!$ciphertext = $this->loadEncrypted($environment)) {
            $this->error("Unable to load encrypted .env file for environment [$environment]");

            return 1;
        }

        $plaintext = $this->envSecurity->decrypt($ciphertext);

        if (!$this->saveDecrypted($plaintext, $this->option('out'))) {
            $this->error("Unable to save decrypted .env file");

            return 1;
        }

        $this->info("Successfully decrypted .env for environment [$environment]");
        return 0;
    }

    /**
     * @return array|string
     */
    protected function environment(): array|string
    {
        return is_null($this->argument('environment'))
            ? $this->envSecurity->resolveEnvironment()
            : $this->argument('environment');
    }
}
