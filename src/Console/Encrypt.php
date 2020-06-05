<?php
namespace STS\EnvSecurity\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;
use STS\EnvSecurity\EnvSecurityManager;

class Encrypt extends Command
{
    use HandlesEnvFiles;

    /**
     * @var string
     */
    protected $signature = 'env:encrypt
                            {environment? : Which environment file you wish to encrypt}';
    /**
     * @var string
     */
    protected $description = 'Encrypt the current .env file in use. Uses current environment name if none provided.';

    /**
     * @var EnvSecurityManager
     */
    protected $envSecurity;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->envSecurity = $envSecurity;

        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!File::isReadable($this->path())) {
            return $this->error("Make sure you have a .env file in your base project path");
        }

        $this->saveEncrypted(
            $this->envSecurity->encrypt(file_get_contents($this->path())),
            $this->environment()
        );

        $this->info("Saved the contents of your current .env file for environment [{$this->environment()}]");
    }

    /**
     * @return string
     */
    protected function environment()
    {
        return $this->argument('environment') ?: config('app.env');
    }

    /**
     * @return string
     */
    protected function path()
    {
        return base_path('.env');
    }
}
