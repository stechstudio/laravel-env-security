<?php

namespace STS\EnvSecurity\Drivers;

use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\DecryptRequest;
use Google\Cloud\Kms\V1\EncryptRequest;
use Illuminate\Contracts\Encryption\Encrypter;

/**
 * Class GoogleKmsDriver
 * @package STS\EnvSecurity\Drivers
 */
class GoogleKmsDriver implements Encrypter
{
    /**
     * @var KeyManagementServiceClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $keyName;

    /**
     * KmsDriver constructor.
     *
     * @param KeyManagementServiceClient $client
     * @param string $project
     * @param string $location
     * @param string $keyRing
     * @param string $key
     */
    public function __construct(KeyManagementServiceClient $client, $project, $location, $keyRing, $key)
    {
        $this->client = $client;
        $this->keyName = $client::cryptoKeyName($project, $location, $keyRing, $key);
    }

    /**
     * @param string $value
     * @param bool   $serialize
     *
     * @return mixed|string
     */
    public function encrypt($value, $serialize = true)
    {
        $request = (new EncryptRequest())
            ->setName($this->keyName)
            ->setPlaintext($value);

        $result = $this->client->encrypt($request)->getCiphertext();

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
    public function decrypt($payload, $unserialize = true)
    {
        if ($unserialize) {
            $payload = base64_decode($payload);
        }

        $request = (new DecryptRequest())
            ->setName($this->keyName)
            ->setCiphertext($payload);

        return $this->client->decrypt($request)->getPlaintext();
    }

    /**
     * @return string
     */
    public function getKey()
    {
        // We have no key to return. This exists purely to comply with the interface.
        return '';
    }

    /**
    * @return array
    */
    public function getAllKeys()
    {
        // We have no keys to return. This exists purely to comply with the interface.
        return [];
    }

    /**
     * Get the previous encryption keys.
     *
     * @return array
     */
    public function getPreviousKeys()
    {
        // We have no keys to return. This exists purely to comply with the interface.
        return [];
    }
}
