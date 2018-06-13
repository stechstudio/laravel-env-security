<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\Console\Concerns;

trait SourceConfiguration
{
    /**
     * If we received the option, prefer it over config.
     *
     * @return string
     */
    protected function getCiphertextFileOption(): string
    {
        $filename = empty($this->option('environment')) ?
            config('kms.file_ciphertext') :
            sprintf(config('kms.ciphertext_filename_template'), $this->option('environment'));

        return sprintf('%s/%s', config('kms.dir_ciphertext'), $filename);
    }

    /**
     * Generate the path from the argument.
     *
     * @param $env
     *
     * @return string
     */
    protected function getCiphertextFileArgument($env): string
    {
        $encryptedFilename = sprintf(config('kms.ciphertext_filename_template'), $env);

        return sprintf('%s/%s', config('kms.dir_ciphertext'), $encryptedFilename);
    }

    /**
     * If we received a specific file, use it.
     *
     * @return string
     */
    protected function getPath(string $key, string $default): string
    {
        return empty($this->option($key)) ?
            $default :
            $this->option($key);
    }
}
