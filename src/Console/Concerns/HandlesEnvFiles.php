<?php

namespace STS\EnvSecurity\Console\Concerns;

/**
 * Class HandlesEnvFiles
 * @package STS\EnvSecurity\Console\Concerns
 */
trait HandlesEnvFiles
{
    /**
     * @param  string  $environment
     *
     * @return null|string
     */
    protected function loadEncrypted(string $environment): ?string
    {
        $path = $this->getFilePathForEnvironment($environment);

        if (is_file($path) && is_readable($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    /**
     * @param  mixed  $ciphertext
     * @param  string  $environment
     *
     * @return bool
     */
    protected function saveEncrypted(mixed $ciphertext, string $environment): bool
    {
        $path = $this->getFilePathForEnvironment($environment);

        return file_put_contents($path, $ciphertext) !== false;
    }

    /**
     * @param  string  $plaintext
     * @param  null|string  $output
     *
     * @return bool
     */
    protected function saveDecrypted(string $plaintext, ?string $output = null): bool
    {
        $output ??= config('env-security.destination');

        return file_put_contents($output, $plaintext) !== false;
    }

    /**
     * @param  string  $environment
     *
     * @return string
     */
    protected function getFilePathForEnvironment(string $environment): string
    {
        return config('env-security.store').'/'.$environment.'.env.enc';
    }
}