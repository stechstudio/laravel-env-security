<?php

namespace STS\EnvSecurity\Pipeline;

use Illuminate\Support\Facades\App;
use STS\EnvSecurity\EnvSecurityManager;
use Illuminate\Contracts\Container\Container;

class Pipeline extends \Illuminate\Pipeline\Pipeline
{
    public function __construct(protected EnvSecurityManager $manager, Container $container = null)
    {
        $container ??= App::getFacadeApplication();
        parent::__construct($container);
    }
}
