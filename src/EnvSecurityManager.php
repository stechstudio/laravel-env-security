<?php

namespace STS\EnvSecurity;

use Aws\Kms\KmsClient;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use STS\EnvSecurity\Drivers\GoogleKmsDriver;
use STS\EnvSecurity\Drivers\KmsDriver;

/**
 *
 */
class EnvSecurityManager extends Manager
{
    /**
     * @var callable
     */
    protected $environmentResolver;

    /**
     * @var callable
     */
    protected $keyResolver;

    /**
     * @var string
     */
    public $environment;

    /**
     * @param callable $callback
     */
    public function resolveEnvironmentUsing($callback)
    {
        $this->environmentResolver = $callback;
    }

    /**
     * @return string|null
     */
    public function resolveEnvironment()
    {
        if($this->environment) {
            return $this->environment;
        }

        return isset($this->environmentResolver)
            ? call_user_func($this->environmentResolver)
            : env('APP_ENV');
    }

    /**
     * Setting an environment name explicitly will override any resolver and default
     *
     * @param $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @param $callback
     */
    public function resolveKeyUsing($callback)
    {
        $this->keyResolver = $callback;
    }

    /**
     * @return string|null
     */
    public function resolveKey()
    {
        return isset($this->keyResolver)
            ? call_user_func($this->keyResolver, $this->resolveEnvironment())
            : null;
    }

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['env-security.default'];
    }

    /**
     * @return KmsDriver
     */
    public function createKmsDriver()
    {
        $config = $this->container['config']['env-security.drivers.kms'];

        $key = $this->keyResolver
            ? $this->resolveKey()
            : $config['key_id'];

        return new KmsDriver(new KmsClient($config), $key);
    }

    /**
     * @return GoogleKmsDriver
     */
    public function createGoogleKmsDriver()
    {
        $config = $this->container['config']['env-security.drivers.google_kms'];

        if ($this->keyResolver) {
            $config['key_id'] = $this->resolveKey();
        }

        $options = Arr::get($config, 'options', []);

        return new GoogleKmsDriver(
            new KeyManagementServiceClient($options),
            Arr::get($config, 'project'),
            Arr::get($config, 'location'),
            Arr::get($config, 'key_ring'),
            Arr::get($config, 'key_id')
        );
    }
}
