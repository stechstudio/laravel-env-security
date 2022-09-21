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

use Google\ApiCore\ApiException;
use Illuminate\Contracts\Encryption\Encrypter;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

/**
 * Class GoogleKmsDriver.
 */
final class GoogleKmsDriver implements Encrypter
{
    /**
     * @var KeyManagementServiceClient
     */
    private KeyManagementServiceClient $client;

    /**
     * @var string
     */
    private string $keyName;

    /**
     * KmsDriver constructor.
     *
     * @param  KeyManagementServiceClient  $client
     * @param  string  $project
     * @param  string  $location
     * @param  string  $keyRing
     * @param  string  $key
     */
    public function __construct(
        KeyManagementServiceClient $client,
        string $project,
        string $location,
        string $keyRing,
        string $key
    ) {
        $this->client = $client;
        $this->keyName = $client::cryptoKeyName($project, $location, $keyRing, $key);
    }

    /**
     * @param  string  $value
     * @param  bool  $serialize
     * @return string
     * @throws ApiException
     */
    public function encrypt($value, $serialize = true): string
    {
        $result = $this->client->encrypt($this->keyName, $value)->getCiphertext();

        return ($serialize)
            ? base64_encode($result)
            : $result;
    }

    /**
     * @param  string  $payload
     * @param  bool  $unserialize
     * @return string
     * @throws ApiException
     */
    public function decrypt($payload, $unserialize = true): string
    {
        if ($unserialize) {
            $payload = base64_decode($payload);
        }

        return $this->client->decrypt($this->keyName, $payload)->getPlaintext();
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
