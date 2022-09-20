<?php

namespace STS\EnvSecurity\Pipeline\Pipes;

use Closure;
use STS\EnvSecurity\Pipeline\Payload;

class Encrypt implements \STS\EnvSecurity\Pipeline\Contracts\Pipe
{

    public function handle(Payload $payload, Closure $next): Payload
    {
        $payload->content = $payload->driver()->encrypt($payload->content);
        $payload->setResolution(false);

        return $next($payload);
    }
}