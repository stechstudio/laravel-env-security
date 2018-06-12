<?php

namespace STS\Kms\DotEnv;

use STS\Kms\DotEnv\Console\DecryptDotFile;
use STS\Kms\DotEnv\Console\DecryptFile;
use STS\Kms\DotEnv\Console\EncryptDotFile;
use STS\Kms\DotEnv\Console\EncryptFile;
use STS\Kms\DotEnv\Crypto\Client;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
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
    protected $configPath = __DIR__.'/../config/kms.php';

    public function boot()
    {
        // helps deal with Lumen vs Laravel differences
        if (function_exists('config_path')) {
            $publishPath = config_path('kms.php');
        } else {
            $publishPath = base_path('config/kms.php');
        }
        $this->publishes([$this->configPath => $publishPath], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DecryptDotFile::class,
                DecryptFile::class,
                EncryptDotFile::class,
                EncryptFile::class,
            ]);
        }
    }

    public function register()
    {
        if (is_a($this->app, 'Laravel\Lumen\Application')) {
            $this->app->configure('kms');
        }
        $this->mergeConfigFrom($this->configPath, 'kms');

        $this->app->singleton(
            Client::class,
            function ($app) {
                return Client::factory(config('kms.kms_key_id'), [config('kms.kms_key_region')]);
            }
        );

        $this->app->alias(Client::class, 'sts.kmsdotenv');
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('KMSDotEnv', KMSDotEnv::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['sts.kmsdotenv', Client::class];
    }
}
