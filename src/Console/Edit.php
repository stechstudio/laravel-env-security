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
use Symfony\Component\Process\Process;

/**
 * Class Edit
 * @package STS\EnvSecurity\Console
 */
class Edit extends Command
{
    use HandlesEnvFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:edit {environment : Which environment file you wish to decrypt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit an encrypted env file';

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
        $this->saveEnvContents(
            $this->edit(
                $this->loadEnvContents()
            )
        );

        $this->info("Successfully updated .env for environment [{$this->environment()}]");
    }

    /**
     * @param $contents
     * @codeCoverageIgnore
     *
     * @return mixed
     */
    protected function edit($contents)
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $contents);
        $meta = stream_get_meta_data($tmpFile);

        $process = new Process([config('env-security.editor'), $meta['uri']]);
        $process->setTty(true);
        $process->mustRun();

        return file_get_contents($meta['uri']);
    }

    /**
     * @return string
     */
    protected function environment()
    {
        return $this->argument('environment');
    }

    /**
     * @return string
     */
    protected function loadEnvContents()
    {
        if($ciphertext = $this->loadEncrypted($this->environment())) {
            return $this->envSecurity->setEnvironment($this->environment())->decrypt($ciphertext);
        }

        return '';
    }

    /**
     * @param $plaintext
     */
    protected function saveEnvContents($plaintext)
    {
        $ciphertext = !empty($plaintext)
            ? $this->envSecurity->encrypt($plaintext)
            : '';

        $this->saveEncrypted($ciphertext, $this->environment());
    }
}
