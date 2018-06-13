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
trait Results
{
    /**
     * Denotes the type of blob we got back, either string or other.
     *
     * @var string
     */
    protected $type;

    /**
     * Result of the last encryption.
     *
     * @var \Aws\Result
     */
    protected $encryptResult;

    /**
     * Result of the last encryption.
     *
     * @var \Aws\Result
     */
    protected $decryptResult;

    /**
     * The base64 encoded ciphertext of the last result.
     *
     * @return string
     */
    public function ciphertextBase64(): string
    {
        return ('string' == $this->type) ?
            base64_encode($this->encryptResult->get('CiphertextBlob')) : $this->encryptResult->get('CiphertextBlob');
    }

    /**
     * Whatever was in the blob of the last result.
     *
     * @return string|resource|StreamInterface
     */
    public function blob()
    {
        return $this->encryptResult->get('CiphertextBlob');
    }

    /**
     * The plaintext of the last result.
     *
     * @return string
     */
    public function plaintext(): string
    {
        return $this->decryptResult->get('Plaintext');
    }
}
