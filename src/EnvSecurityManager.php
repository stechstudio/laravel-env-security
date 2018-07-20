<?php

namespace STS\EnvSecurity;

use Aws\Kms\KmsClient;
use Illuminate\Support\Manager;
use STS\EnvSecurity\Drivers\KmsDriver;

class EnvSecurityManager extends Manager
{
    /**
     * @var callable
     */
    protected $environmentResolver;

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

        return new KmsDriver(new KmsClient($config), $config['key_id']);
    }
}