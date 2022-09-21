<?php
/*
 * This file is part of laravel-env-security.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\EnvSecurity\Drivers;

use Aws\Kms\KmsClient;
use Illuminate\Contracts\Encryption\Encrypter;

/**
 * Class KmsDriver.
 */
final class KmsDriver implements Encrypter
{
    /**
     * @var KmsClient
     */
    private KmsClient $client;

    /**
     * @var string
     */
    private string $keyId;

    /**
     * KmsDriver constructor.
     *
     * @param $kmsClient
     * @param $keyId
     */
    public function __construct($kmsClient, $keyId)
    {
        $this->client = $kmsClient;
        $this->keyId = $keyId ?? '';
    }

    /**
     * @param  string  $value
     * @param  bool  $serialize
     *
     * @return mixed|string
     */
    public function encrypt($value, $serialize = null): mixed
    {
        $serialize ??= true;

        $result = $this->client->encrypt([
            'KeyId' => $this->keyId,
            'Plaintext' => $value,
        ])->get('CiphertextBlob');

        return ($serialize)
            ? base64_encode($result)
            : $result;
    }

    /**
     * @param  string  $payload
     * @param  bool  $unserialize
     *
     * @return string
     */
    public function decrypt($payload, $unserialize = null): string
    {
        $unserialize ??= true;

        if ($unserialize) {
            $payload = base64_decode($payload);
        }

        return $this->client->decrypt([
            'KeyId' => $this->keyId,
            'CiphertextBlob' => $payload,
        ])->get('Plaintext');
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        // We have no key to return. This exists purely to comply with the interface.
        return '';
    }
}
