<?php

namespace STS\EnvSecurity;

use Aws\Kms\KmsClient;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use STS\EnvSecurity\Drivers\GoogleKmsDriver;
use STS\EnvSecurity\Drivers\KmsDriver;

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
        return isset($this->environmentResolver)
            ? call_user_func($this->environmentResolver)
            : env('APP_ENV');
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
        return $this->app['config']['env-security.default'];
    }

    /**
     * @return KmsDriver
     */
    public function createKmsDriver()
    {
        $config = $this->app['config']['env-security.drivers.kms'];

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
        $config = $this->app['config']['env-security.drivers.google_kms'];

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
