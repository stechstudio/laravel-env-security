<?php

namespace Tests;

use STS\EnvSecurity\Console\Edit;

/**
 * Test double for our Edit command
 */
class EditDouble extends Edit
{
    protected $signature = 'sts-env:edit {environment : Which environment file you wish to decrypt} {--c|compress : Override configuration and require compression.} {--append=} ';

    protected function environment()
    {
        return "testing";
    }

    protected function edit($contents)
    {
        $this->info('Plaintext contents: '.$contents);

        if ($this->option('append')) {
            $contents = trim($contents." ".$this->option('append'));
        }

        return $contents;
    }
}
