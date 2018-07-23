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
use STS\EnvSecurity\Console\Decrypt;
use STS\EnvSecurity\Console\Edit;

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

        if (!class_exists(\EnvSecurity::class)) {
            class_alias(EnvSecurityFacade::class, \EnvSecurity::class);
        }
    }

    public function boot()
    {
        // helps deal with Lumen vs Laravel differences
        if (function_exists('config_path')) {
            $publishPath = config_path('env-security.php');
        } else {
            $publishPath = base_path('config/env-security.php');
        }
        $this->publishes([$this->configPath => $publishPath], 'config');

        if (!is_dir(config('env-security.store'))) {
            if (!mkdir(config('env-security.store'))) {
                throw new ConfigurationException(
                    sprintf('Error creating the cipertext directory - %s', config('env-security.store'))
                );
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                Decrypt::class,
                Edit::class,
            ]);
        }
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
}
