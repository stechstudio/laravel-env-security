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

use STS\EnvSecurity\Console\Encrypt;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use STS\EnvSecurity\Console\Decrypt;
use STS\EnvSecurity\Console\Edit;
use function config;
use function sprintf;

class EnvSecurityServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Default path to configuration.
     *
     * @var string
     */
    protected $configPath = __DIR__ . '/../config/env-security.php';

    public function boot()
    {
        // helps deal with Lumen vs Laravel differences
        if (function_exists('config_path')) {
            $publishPath = config_path('env-security.php');
        } else {
            $publishPath = base_path('config/env-security.php');
        }
        $this->publishes([$this->configPath => $publishPath], 'config');

        $this->verifyDirectory();

        // If TTY mode is enabled, verify that it is supported.
        if (config('env.tty_mode')) {
            $this->verifyTtySupport();
        }

        if ($this->app->runningInConsole()) {
            $this->commands($this->getConsoleCommands());
        }
    }

    /**
     * Determines whether TTY is supported on the current operating system.
     * 
     * @return void 
     * @throws RuntimeException 
     */
    public function verifyTtySupport(): void
    {
        // If this is PHP compiled for windows, ensure the developer understands
        // TTY mode is not a valid option at all.
        if ('\\' === \DIRECTORY_SEPARATOR) {
            throw new RuntimeException('TTY mode is not supported in PHP for the Windows platform. Please disable TTY_MODE=false in your configuration.');
        }

        $result = (bool) @proc_open('echo 1 >/dev/null', [['file', '/dev/tty', 'r'], ['file', '/dev/tty', 'w'], ['file', '/dev/tty', 'w']], $pipes);

        if ($result !== true) {
            throw new RuntimeException('TTY mode is not supported .');
        }
    }


    /**
     * Make sure our directory is setup and ready
     */
    protected function verifyDirectory()
    {
        try {
            if (!is_dir(config('env-security.store'))) {
                if (!mkdir(config('env-security.store'))) {
                    throw new RuntimeException(
                        sprintf('Error creating the cipertext directory - %s', config('env-security.store'))
                    );
                }
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
    protected function getConsoleCommands()
    {
        return [Decrypt::class, Edit::class, Encrypt::class];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['sts.env-security', EnvSecurityManager::class];
    }

    public function register()
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
