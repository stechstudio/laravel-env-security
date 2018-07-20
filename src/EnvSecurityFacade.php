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

/**
 * Class Profile.
 *
 *
 * @method static EnvSecurityManager factory(string $keyId, array $config = [])
 * @method static EnvSecurityManager encrypt($plaintext, $serialize = true)
 * @method static EnvSecurityManager decrypt($plaintext, $serialize = true)
 * @method static EnvSecurityManager resolveEnvironmentUsing($callback)
 * @method static EnvSecurityManager resolveEnvironment()
 */
class EnvSecurityFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sts.env-security';
    }
}
