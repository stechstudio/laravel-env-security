<?php

namespace STS\EnvSecurity\Drivers;

use Google\Cloud\Kms\V1\KeyManagementServiceClient;
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
        $result = $this->client->encrypt($this->keyName, $value)->getCiphertext();

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

        return $this->client->decrypt($this->keyName, $payload)->getPlaintext();
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
