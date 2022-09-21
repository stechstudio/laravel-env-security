<?php

namespace STS\EnvSecurity\Pipeline\Pipes;

use Closure;
use RuntimeException;
use STS\EnvSecurity\Pipeline\Payload;

class WriteFile implements \STS\EnvSecurity\Pipeline\Contracts\Pipe
{
    public function handle(Payload $payload, Closure $next): Payload
    {
        if (file_put_contents(
            filename: $payload->getDestinationFilePath(),
            data: $payload->content
        ) === false) {
            throw new RuntimeException("Failed to write to {$payload->getDestinationFilePath()}");
        }

        $payload->setResolution(true);

        return $payload;
    }
}
