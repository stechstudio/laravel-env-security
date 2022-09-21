<?php

namespace Tests;

use STS\EnvSecurity\Console\Decrypt;

/**
 * Test double for our Edit command.
 */
class DecryptDouble extends Decrypt
{
    public function handle(): int
    {
        $result = parent::handle();

        $this->info('Used key ['.$this->envSecurity->resolveKey().']');

        return $result;
    }
}
