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

trait RegionConfiguration
{
    /**
     * If we receive a region, prefer it.
     *
     * @return array
     */
    public function configureRegion(): array
    {
        if (is_null($this->option('region'))) {
            return ['region' => config('kms.kms_key_region')];
        }

        return ['region' => $this->option('region')];
    }
}
