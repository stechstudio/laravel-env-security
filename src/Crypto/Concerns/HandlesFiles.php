<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\Crypto\Concerns;

use STS\Kms\DotEnv\Crypto\Client;

/**
 * Trait HandlesFiles.
 *
 * @mixin Client
 */
trait HandlesFiles
{
    /**
     * Decrypts a ciphertext file.
     *
     * @param string $path
     * @param array  $options
     *
     * @return Client
     */
    public function decryptFile(string $path, array $options = []): Client
    {
        $this->decrypt(base64_decode(file_get_contents($path)), $this->getOptions($options));

        return $this;
    }

    /**
     * Encrypts a plaintext file.
     *
     * @param string $path
     * @param array  $options
     *
     * @return Client
     */
    public function encryptFile(string $path, array $options = []): Client
    {
        $this->encrypt(file_get_contents($path), $this->getOptions($options));

        return $this;
    }

    /**
     * Saves ciphertext to a file.
     *
     * @param string $path
     *
     * @return Client
     */
    public function saveEncryptedFile(string $path): Client
    {
        file_put_contents(
            sprintf($path),
            $this->ciphertextBase64()
        );

        return $this;
    }

    /**
     * Saves Plaintext to a file.
     *
     * @param string $path
     *
     * @return Client
     */
    public function saveDecryptedFile(string $path): Client
    {
        file_put_contents(
            sprintf($path),
            $this->plaintext()
        );

        return $this;
    }

    /**
     * Decrypts a ciphertext file to a tempfile, opens it up in an editor. When the editor exits, takes that edited
     * plaintext, encrypts it, and writes the encryption back to the ciphertext file.
     *
     * @param string $editorPath
     * @param string $path
     * @param array  $options
     *
     * @return Client
     */
    public function editEncryptedFile(string $editorPath, string $path, array $options = []): Client
    {
        $this->decryptFile($path, $this->getOptions($options));

        $tmpFile = tmpfile();
        fwrite($tmpFile, $this->plaintext());
        $meta = stream_get_meta_data($tmpFile);

        $process = new \Symfony\Component\Process\Process([$editorPath, $meta['uri']]);
        $process->setTty(true);
        $process->mustRun();

        $this->encryptFile($meta['uri'], $this->getOptions($options));
        $this->saveEncryptedFile($path);

        return $this;
    }

    public function copyEncryptedFile(string $editorPath, string $src, string $dest, array $options = []): Client
    {
        $this->decryptFile($src, $this->getOptions($options));

        $tmpFile = tmpfile();
        fwrite($tmpFile, $this->plaintext());
        $meta = stream_get_meta_data($tmpFile);

        $process = new \Symfony\Component\Process\Process([$editorPath, $meta['uri']]);
        $process->setTty(true);
        $process->mustRun();

        $this->encryptFile($meta['uri'], $this->getOptions($options));
        $this->saveEncryptedFile($dest);

        return $this;
    }
}
