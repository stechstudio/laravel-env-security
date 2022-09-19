<?php

namespace STS\EnvSecurity\Drivers;

use Aws\Kms\KmsClient;
use Illuminate\Contracts\Encryption\Encrypter;

/**
 * Class KmsDriver
 * @package STS\EnvSecurity\Drivers
 */
class KmsDriver implements Encrypter
{
    /**
     * @var KmsClient
     */
    protected $client;

    /**
     * @var
     */
    protected $keyId;

    /**
     * KmsDriver constructor.
     *
     * @param $kmsClient
     * @param $keyId
     */
    public function __construct($kmsClient, $keyId)
    {
        $this->client = $kmsClient;
        $this->keyId = $keyId;
    }

    /**
     * @param string $value
     * @param bool   $serialize
     *
     * @return mixed|string
     */
    public function encrypt($value, $serialize = true): mixed
    {
        $result = $this->client->encrypt([
            'KeyId'     => $this->keyId,
            'Plaintext' => $value
        ])->get('CiphertextBlob');

        return ($serialize)
            ? base64_encode($result)
            : $result;
    }

    /**
     * @param string $payload
     * @param bool   $unserialize
     *
     * @return string
     */
    public function decrypt($payload, $unserialize = true): string
    {
        if ($unserialize) {
            $payload = base64_decode($payload);
        }

        return $this->client->decrypt([
            'KeyId'          => $this->keyId,
            'CiphertextBlob' => $payload
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