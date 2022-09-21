<?php
/*
 * This file is part of laravel-env-security.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\EnvSecurity;

use Illuminate\Support\Facades\Facade;
use STS\EnvSecurity\Drivers\GoogleKmsDriver;
use STS\EnvSecurity\Drivers\KmsDriver;

/**
 * Class Profile.
 *
 *
 * @method static void resolveEnvironmentUsing(callable $callback)
 * @method static string|null resolveEnvironment()
 * @method static EnvSecurityManager setEnvironment(?string $environment)
 * @method static void resolveKeyUsing(callable $callback)
 * @method static string|null resolveKey()
 * @method static string getDefaultDriver()
 * @method static KmsDriver createKmsDriver()
 * @method static GoogleKmsDriver createGoogleKmsDriver()
 * @method static string encrypt(string $value, bool $serialize = true)
 * @method static string decrypt(string $value, bool $deserialize = true)
 */
final class EnvSecurityFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sts.env-security';
    }
}
