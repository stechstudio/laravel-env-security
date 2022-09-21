<?php

namespace STS\EnvSecurity\Pipeline\Pipes;

use Closure;
use RuntimeException;
use STS\EnvSecurity\Pipeline\Payload;

final class Compress implements \STS\EnvSecurity\Pipeline\Contracts\Pipe
{
    public function handle(Payload $payload, Closure $next): Payload
    {
        if (config('env-security.enable_compression')) {
            $payload->checkZlibExtension('Laravel Env Security compression is enabled, but the zlib extension is not installed.');
            /* @noinspection PhpComposerExtensionStubsInspection */
            if (($compressed = gzencode($payload->content, 9)) === false) {
                throw new RuntimeException('Failed to compress the content.');
            }

            $payload->content = "gzencoded::$compressed";
        }
        $payload->setResolution(false);

        return $next($payload);
    }
}
