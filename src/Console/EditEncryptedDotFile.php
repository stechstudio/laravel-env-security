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

class EditEncryptedDotFile extends Command
{
    use SourceConfiguration;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dotenv:edit {environment : The environment file to decrypt.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit an encrypted dotfile.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            KMSDotEnv::editEncryptedFile(
                config('kms.editor'),
                $this->getCiphertextFileArgument($this->argument('environment'))
            );
        } catch (ConfigurationException $e) {
            $this->error('You need to configure kmsdotenv.');

            return 1;
        }

        $this->info(sprintf('Successfully updated %s.', $this->getCiphertextFileArgument($this->argument('environment'))));

        return 0;
    }
}
