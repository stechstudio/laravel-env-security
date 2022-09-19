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

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use STS\EnvSecurity\Console\Decrypt;
use STS\EnvSecurity\Console\Edit;
use STS\EnvSecurity\Console\Encrypt;
use function config;
use function sprintf;

class EnvSecurityServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    /**
     * Default path to configuration.
     *
     * @var string
     */
    protected string $configPath = __DIR__.'/../config/env-security.php';

    public function boot(): void
    {
        // helps deal with Lumen vs Laravel differences
        if (function_exists('config_path')) {
            $publishPath = config_path('env-security.php');
        } else {
            $publishPath = base_path('config/env-security.php');
        }
        $this->publishes([$this->configPath => $publishPath], 'config');

        $this->verifyDirectory();

        if ($this->app->runningInConsole()) {
            $this->commands($this->getConsoleCommands());
        }
    }

    /**
     * Make sure our directory is setup and ready
     */
    protected function verifyDirectory(): void
    {
        try {
            if (!is_dir(config('env-security.store')) && !mkdir($concurrentDirectory = config('env-security.store')) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(
                    sprintf('Error creating the cipertext directory - %s', config('env-security.store'))
                );
            }
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf('Error creating the cipertext directory - %s', config('env-security.store')),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Register our console commands
     */
    protected function getConsoleCommands(): array
    {
        return [Decrypt::class, Edit::class, Encrypt::class];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['sts.env-security', EnvSecurityManager::class];
    }

    public function register(): void
    {
        $this->app->singleton(EnvSecurityManager::class, function () {
            return new EnvSecurityManager($this->app);
        });
        $this->app->alias(EnvSecurityManager::class, 'sts.env-security');

        if (is_a($this->app, 'Laravel\Lumen\Application')) {
            $this->app->configure('env-security');
        }
        $this->mergeConfigFrom($this->configPath, 'env-security');
    }
}
