<?php

namespace Tests;

use STS\EnvSecurity\Console\Decrypt;
use STS\EnvSecurity\EnvSecurityServiceProvider;

class ServiceProviderDouble extends EnvSecurityServiceProvider
{
    /**
     * Register our console commands
     */
    protected function getConsoleCommands()
    {
        return [Decrypt::class, EditDouble::class];
    }
}