<?php
/*
 * This file is part of kmsdotenv.
 *
 *  (c) Signature Tech Studio, Inc <info@stechstudio.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace STS\Kms\DotEnv\TextUI;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Edit extends SymfonyCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('kms:edit')

            // the short description shown while running "php bin/console list"
            ->setDescription('Edit and encrypted file.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to edit an encrypted file with a KMS key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 'Edit Here';
    }
}
