<?php

namespace STS\EnvSecurity\Pipeline\Contracts;

use Closure;
use STS\EnvSecurity\Pipeline\Payload;

interface Pipe
{
    public function handle(Payload $payload, Closure $next): Payload;
}