<?php

namespace STS\EnvSecurity\Console\Concerns;

/**
 * Class HandlesEnvFiles
 * @package STS\EnvSecurity\Console\Concerns
 */
trait HandlesEnvFiles
{
    /**
     * @param $environment
     *
     * @return bool|null|string
     */
    protected function loadEncrypted($environment)
    {
        $path = $this->getFilePathForEnvironment($environment);

        if (file_exists($path) && is_readable($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    /**
     * @param $ciphertext
     * @param $environment
     *
     * @return bool
     */
    protected function saveEncrypted($ciphertext, $environment)
    {
        $path = $this->getFilePathForEnvironment($environment);

        return file_put_contents($path, $ciphertext) !== false;
    }

    /**
     * @param      $plaintext
     * @param null $output
     *
     * @return bool
     */
    protected function saveDecrypted($plaintext, $output = null)
    {
        if (!$output) {
            $output = config('env-security.destination');
        }

        return file_put_contents($output, $plaintext) !== false;
    }

    /**
     * @param $environment
     *
     * @return string
     */
    protected function getFilePathForEnvironment($environment)
    {
        return config('env-security.store') . '/' . $environment . '.env.enc';
    }
}