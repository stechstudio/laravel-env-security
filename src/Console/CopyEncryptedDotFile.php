<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\Console;

use Illuminate\Console\Command;
use STS\Kms\DotEnv\Console\Concerns\SourceConfiguration;
use STS\Kms\DotEnv\Facades\KMSDotEnv;

class CopyEncryptedDotFile extends Command
{
    use SourceConfiguration;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:copy 
                            {src-environment : The environment of the file to copy.} 
                            {dest-environment : The environment of the file to create.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy an encrypted dotfile.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            KMSDotEnv::copyEncryptedFile(
                config('kms.editor'),
                $this->getCiphertextFileArgument($this->argument('src-environment')),
                $this->getCiphertextFileArgument($this->argument('dest-environment'))
            );
        } catch (ConfigurationException $e) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        $this->info(sprintf(
            'Successfully coppied %s to %s.',
            $this->getCiphertextFileArgument($this->argument('src-environment')),
            $this->getCiphertextFileArgument($this->argument('dest-environment'))
        ));

        return 0;
    }
}
