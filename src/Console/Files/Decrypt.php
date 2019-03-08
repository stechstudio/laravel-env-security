<?php declare(strict_types=1);

/**
 * Package: laravel-env-security
 * Create Date: 2019-03-08
 * Created Time: 15:56
 */

namespace STS\EnvSecurity\Console\Files;

use Illuminate\Console\Command;
use STS\EnvSecurity\EnvSecurityManager;
use function base_path;

class Decrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:decrypt-file
                            {src : File you wish to decrypt.}
                            {dest : Where to write the decrypted file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypts any file with a valid path to a destination.';

    /** @var EnvSecurityManager */
    protected $envSecurity;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->envSecurity = $envSecurity;

        parent::__construct();
    }

    public function handle(): int
    {
        $source = $this->arguments('src');
        $destination = $this->arguments('dest');
        $plaintext = $this->envSecurity->decrypt(file_get_contents(base_path('env/' . basename($source))));
        file_put_contents(base_path($destination), $plaintext);
        return 0;
    }
}
