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

use STS\Kms\DotEnv\Crypto\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Decrypt extends Command
{
    protected function configure()
    {
        $this
            ->setName('kms:decrypt')
            ->setDescription('Decrypt a file.')
            ->setHelp('This command allows you to decrypt a file with a KMS key.')
            ->addArgument('in', InputArgument::REQUIRED, 'File path to encrypt.')
            ->addArgument('out', InputArgument::REQUIRED, 'File path to encrypt.')
            ->addOption(
                'kmsid',
                'k',
                InputOption::VALUE_REQUIRED,
                'KMS Key ID or Alias'
            )
            ->addOption(
                'region',
                'r',
                InputOption::VALUE_REQUIRED,
                'AWS Region to use.',
                'us-east-1'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Client($input->getOption('kmsid'), ['region' => $input->getOption('region')]))
            ->decryptFile($input->getArgument('in'))
            ->saveDecryptedFile($input->getArgument('out'));
        $output->writeln(sprintf('%s was saved.', $input->getArgument('out')));
    }
}
