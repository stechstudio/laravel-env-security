<?php

namespace STS\EnvSecurity;

use Aws\Kms\KmsClient;
use ErrorException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use RuntimeException;
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
     * @param  callable  $callback
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
        if ($this->environment) {
            return $this->environment;
        }

        return isset($this->environmentResolver)
            ? call_user_func($this->environmentResolver)
            : config('app.env');
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
        return config('env-security.default');
    }

    /**
     * @return KmsDriver
     */
    public function createKmsDriver()
    {
        $config = config('env-security.drivers.kms');

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
        $config = config('env-security.drivers.google_kms');

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

    /**
     * Encrypt the value.
     *
     * @param  string  $value
     * @param  bool  $serialize
     * @return string
     */
    public function encrypt($value, $serialize = true)
    {
        // Compress Value
        if (config('env-security.enable_compression')) {
            $this->checkZlibExtension();
            $value = gzencode($value, 9);
        }
        // Encode Value
        return $this->driver()->encrypt($value, $serialize);
    }

    /**
     * Decrypt the value.
     *
     * @param  string  $value
     * @param  bool  $unserialize
     * @return string
     */
    public function decrypt($value, $unserialize = true)
    {
        $value = $this->driver()->decrypt($value, $unserialize);

        // De-compress Value
        if (config('env-security.enable_compression')) {
            $this->checkZlibExtension();
            $decodedValue = gzdecode($value);

            // @todo Determine if this should be default behavior
            // Failed to decompress the value, returning the value instead.
            $value = $decodedValue !== false ? $decodedValue : $value;
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     * @throws RuntimeException
     */
    private function decompress($value)
    {
        $this->checkZlibExtension('The environment file was compressed and can not be decompressed because the zlib extension is not installed.');
        try {
            \set_error_handler(
                static function ($errno, $errstr, $errfile, $errline) {
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                },
                E_WARNING);
            $result = gzdecode(Str::substr($value, strlen('gzencoded::')));
        } catch (ErrorException $previous) {
            throw new RuntimeException(
                'The unencrypted data is corrupt and can not be uncompressed.',
                0,
                $previous
            );
        } finally {
            \restore_error_handler();
        }

        return $result;
    }

    private function checkZlibExtension($message)
    {
        if (!in_array('zlib', get_loaded_extensions())) {
            throw new RuntimeException($message);
        }
    }

}
