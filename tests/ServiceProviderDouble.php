<?php

namespace Tests;

use STS\EnvSecurity\EnvSecurityServiceProvider;

class ServiceProviderDouble extends EnvSecurityServiceProvider
{
    /**
     * Register our console commands
     */
    protected function getConsoleCommands(): array
    {
        return [DecryptDouble::class, EditDouble::class];
    }
}