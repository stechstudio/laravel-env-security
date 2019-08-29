<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;
use STS\EnvSecurity\EnvSecurityManager;

class Encrypt extends Command
{
    use HandlesEnvFiles;

    /** @var string */
    protected $signature = 'env:encrypt
                            {environment? : Which environment file you wish to encrypt}
                            {--o|out= : Saves the encrypted file to an alternate location}';
    /** @var string */
    protected $description = 'Encrypt a .env file. Tries to deduce the environment if none provided.';

    /** @var EnvSecurityManager */
    protected $envSecurity;

    /** @var string */
    protected $dotEnvPath;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->envSecurity = $envSecurity;
        $this->dotEnvPath = base_path('.env');
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $environment = $this->argument('environment') ?: config('app.env');
        if (File::exists($this->dotEnvPath) && File::isReadable($this->dotEnvPath)) {
            $envFileContents = file_get_contents($this->dotEnvPath);
            $envCiphertext = $this->envSecurity->encrypt($envFileContents);
            $this->saveEncrypted($envCiphertext, $environment);
            $this->info(sprintf('Saved the contents of %s to %s', $this->dotEnvPath, $this->getFilePathForEnvironment($environment)));
        }

        return null;
    }

}
