<?php
/*
 * This file is part of laravel-env-security.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\EnvSecurity;

use Aws\Kms\KmsClient;
use Closure;
use Google\ApiCore\ValidationException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager;
use STS\EnvSecurity\Drivers\GoogleKmsDriver;
use STS\EnvSecurity\Drivers\KmsDriver;
use STS\EnvSecurity\Pipeline\Payload;
use STS\EnvSecurity\Pipeline\Pipeline;
use STS\EnvSecurity\Pipeline\Pipes\Compress;
use STS\EnvSecurity\Pipeline\Pipes\Decompress;
use STS\EnvSecurity\Pipeline\Pipes\Decrypt;
use STS\EnvSecurity\Pipeline\Pipes\Encrypt;
use STS\EnvSecurity\Pipeline\Pipes\ReadFile;
use STS\EnvSecurity\Pipeline\Pipes\WriteFile;

class EnvSecurityManager extends Manager
{
    /**
     * @var Closure
     */
    protected Closure $environmentResolver;

    /**
     * @var Closure
     */
    protected Closure $keyResolver;

    /**
     * @var ?string
     */
    public ?string $environment;

    /**
     * @param  callable  $callback
     */
    public function resolveEnvironmentUsing(callable $callback): void
    {
        $this->environmentResolver = Closure::fromCallable($callback);
    }

    /**
     * @return string|null
     */
    public function resolveEnvironment(): ?string
    {
        return $this->environment ??= isset($this->environmentResolver)
            ? \call_user_func($this->environmentResolver)
            : config('app.env');
    }

    /**
     * Setting an environment name explicitly will override any resolver.
     *
     * @param ?string  $environment
     *
     * @return $this
     */
    public function setEnvironment(?string $environment): static
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @param  callable  $callback
     */
    public function resolveKeyUsing(callable $callback): void
    {
        $this->keyResolver = Closure::fromCallable($callback);
    }

    /**
     * @return string|null
     */
    public function resolveKey(): ?string
    {
        return isset($this->keyResolver)
            ? \call_user_func($this->keyResolver, $this->resolveEnvironment())
            : null;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('env-security.default');
    }

    /**
     * @return KmsDriver
     */
    public function createKmsDriver(): KmsDriver
    {
        $config = config('env-security.drivers.kms');

        $key = $this->resolveKey() ?? $config['key_id'];

        return new KmsDriver(new KmsClient($config), $key);
    }

    /**
     * @return GoogleKmsDriver
     * @throws ValidationException
     */
    public function createGoogleKmsDriver(): GoogleKmsDriver
    {
        $config = config('env-security.drivers.google_kms');

        $config['key_id'] = $this->resolveKey() ?? $config['key_id'];

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
     */
    public function encrypt(?string $environment = null): void
    {
        $environment ??= $this->resolveEnvironment();

        (new Pipeline($this))->send(
            new Payload(
                environment: $environment,
                manager: $this,
                operation: Payload::ENCRYPT
            )
        )->through(
            [
                // Load current .env file.
                ReadFile::class,
                // Compress? current .env file.
                Compress::class,
                // Encrypt Compressed? .env file
                Encrypt::class,
                // Save Compressed .env file
                WriteFile::class,
            ]
        );
    }

    /**
     * Decrypt the value.
     */
    public function decrypt(?string $environment = null): void
    {
        $environment ??= $this->resolveEnvironment();

        (new Pipeline($this))->send(
            new Payload(
                environment: $environment,
                manager: $this,
                operation: Payload::ENCRYPT
            )
        )->through(
            [
                // Load encrypted file
                ReadFile::class,
                // Decrypted encrypted file
                Decrypt::class,
                // decompress
                Decompress::class,
                // save as current unencrypted file
                WriteFile::class,
            ]
        );
    }
}
