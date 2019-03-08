<?php declare(strict_types=1);

/**
 * Package: laravel-env-security
 * Create Date: 2019-03-08
 * Created Time: 15:01
 */

namespace STS\EnvSecurity\Console\Files;

use Illuminate\Console\Command;
use STS\EnvSecurity\EnvSecurityManager;
use function base_path;
use function file_get_contents;
use function file_put_contents;
use function is_file;

class Encrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:encrypt-file
                            {file? : Path to which file you wish to encrypt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypts any file with a valid path.';

    /** @var EnvSecurityManager */
    protected $envSecurity;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->envSecurity = $envSecurity;

        parent::__construct();
    }

    public function handle(): int
    {
        // get the argument
        $source = $this->arguments('file');
        // try to find the file
        $source = is_file($source) ?
            $source :
            is_file(base_path($source)) ?
                base_path($source) :
                false;
        // pitch a fit if we can't
        if ($source === false) {
            throw new \RuntimeException('Can not find that file.');
        }
        $destination = base_path('env/' . basename($source));
        $plaintext = file_get_contents($source);
        $ciphertext = $this->envSecurity->encrypt($this->edit($plaintext));
        file_put_contents($destination, $ciphertext);
        return 0;
    }
}
