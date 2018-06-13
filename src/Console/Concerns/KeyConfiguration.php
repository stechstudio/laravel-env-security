<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\Console\Concerns;

use STS\Kms\DotEnv\Facades\KMSDotEnv;

trait KeyConfiguration
{
    /**
     * If we receive a key, use it.
     */
    public function configureKey(): void
    {
        if (! empty($this->option('kmsid'))) {
            KMSDotEnv::setKeyId($this->option('kmsid'));
        }
    }
}
