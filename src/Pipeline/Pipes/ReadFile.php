<?php

namespace STS\EnvSecurity\Pipeline\Pipes;

use Closure;
use RuntimeException;
use STS\EnvSecurity\Pipeline\Payload;

class ReadFile implements \STS\EnvSecurity\Pipeline\Contracts\Pipe
{

    public function handle(Payload $payload, Closure $next): Payload
    {
        if (($payload->content = file_get_contents($payload->getSourceFilePath())) === false) {
            throw new RuntimeException("Failed to read to {$payload->getSourceFilePath()}");
        }
        $payload->setResolution(false);

        return $next($payload);
    }
}