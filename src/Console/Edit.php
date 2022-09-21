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
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;
use STS\EnvSecurity\EnvSecurityManager;
use STS\EnvSecurity\Console\Concerns\HandlesEnvFiles;

/**
 * Class Edit.
 */
class Edit extends Command
{
    use HandlesEnvFiles;
    /**
     * @var EnvSecurityManager
     */
    protected EnvSecurityManager $envSecurity;

    public function __construct(EnvSecurityManager $envSecurity)
    {
        $this->signature = 'env:edit 
                            {environment : Which environment file you wish to edit}
                            {--C|compress : Override configuration and require compression.}';
        $this->description = 'Edit an encrypted env file';
        $this->envSecurity = $envSecurity;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        if ($this->option('compress')) {
            Config::set('env-security.enable_compression', true);
        }
        $this->envSecurity->setEnvironment($this->environment());

        $this->saveEnvContents(
            $this->edit(
                $this->loadEnvContents()
            )
        );

        $this->info("Successfully updated .env for environment [{$this->environment()}]");

        return 0;
    }

    /**
     * @param $contents
     * @codeCoverageIgnore
     *
     * @return string|bool
     */
    protected function edit($contents): string|bool
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $contents);
        $meta = stream_get_meta_data($tmpFile);

        $process = new Process([config('env-security.editor'), $meta['uri']]);
        $process->setTimeout(null);
        $process->setTty(Process::isTtySupported());
        $process->mustRun();
        if (! Process::isTtySupported()) {
            while (empty(file_get_contents($meta['uri'])) || file_get_contents($meta['uri']) === $contents) {
                sleep(2);
            }
        }

        return file_get_contents($meta['uri']);
    }

    /**
     * @return string
     */
    protected function environment(): string
    {
        return $this->argument('environment');
    }

    /**
     * @return string
     */
    protected function loadEnvContents(): string
    {
        if ($ciphertext = $this->loadEncrypted($this->environment())) {
            return $this->envSecurity->decrypt($ciphertext);
        }

        return '';
    }

    /**
     * @param $plaintext
     */
    protected function saveEnvContents($plaintext): void
    {
        $ciphertext = ! empty($plaintext)
            ? $this->envSecurity->encrypt($plaintext)
            : '';

        $this->saveEncrypted($ciphertext, $this->environment());
    }
}
