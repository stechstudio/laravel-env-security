<?php

namespace STS\EnvSecurity\Pipeline\Pipes;

use Closure;
use ErrorException;
use Illuminate\Support\Str;
use RuntimeException;
use STS\EnvSecurity\Pipeline\Payload;

class Decompress implements \STS\EnvSecurity\Pipeline\Contracts\Pipe
{
    public function handle(Payload $payload, Closure $next): Payload
    {
        if (Str::substr($payload->content, 0, \strlen('gzencoded::')) === 'gzencoded::') {
            $this->decompress($payload);
        }

        $payload->setResolution(false);

        return $next($payload);
    }

    /**
     * @param  Payload  $payload
     * @throws RuntimeException
     */
    private function decompress(Payload $payload): void
    {
        $payload->checkZlibExtension('The environment file was compressed and can not be decompressed because the zlib extension is not installed.');
        try {
            $this->setErrorHandler();
            /* @noinspection PhpComposerExtensionStubsInspection */
            $payload->content = gzdecode(Str::substr($payload->content, \strlen('gzencoded::')));
        } catch (ErrorException $previous) {
            throw new RuntimeException(
                'The unencrypted data is corrupt and can not be uncompressed.',
                0,
                $previous
            );
        } finally {
            restore_error_handler();
        }
    }

    private function setErrorHandler(): void
    {
        set_error_handler(
        /**
         * @throws ErrorException
         */
            static fn($errno, $errstr, $errfile, $errline) => throw new \ErrorException(
                $errstr,
                0,
                $errno,
                $errfile,
                $errline
            ),
            E_WARNING
        );
    }
}
