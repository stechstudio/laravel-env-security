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
 * Class Decrypt.
 */
class Decrypt extends Command
{
    use HandlesEnvFiles;

    /**
     * @var EnvSecurityManager
     */
    protected EnvSecurityManager $envSecurity;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->signature = 'env:decrypt
                            {environment? : Which environment file you wish to decrypt}
                            {--o|out= : Saves the decrypted file to an alternate location}';

        $this->description = 'Decrypt a .env file. Tries to deduce the environment if none provided.';
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
        if (!$environment = $this->environment()) {
            $this->error("No environment specified, and we couldn't resolve it on our own");

            return 1;
        }

        $this->envSecurity->decrypt($this->environment());

        $this->info("Successfully decrypted .env for environment [$environment]");

        return 0;
    }

    /**
     * @return array|string
     */
    protected function environment(): array|string
    {
        return $this->argument('environment') ?? $this->envSecurity->resolveEnvironment();
    }
}
