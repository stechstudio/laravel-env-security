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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Encrypt extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('kms:encrypt')
            ->setDescription('Encrypt a file.')
            ->setHelp('This command allows you to encrypt a file with a KMS key.')
            ->addArgument('file', InputArgument::REQUIRED, 'File path to encrypt.')
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
        $kms = new \Aws\Kms\KmsClient([
            'version' => 'latest',
            'region' => $input->getOption('region'),
        ]);

        $result = $kms->encrypt([
            'KeyId' => $input->getOption('kmsid'),
            'Plaintext' => file_get_contents($input->getArgument('file')),
        ]);

        file_put_contents(
            sprintf('%s.enc', $input->getArgument('file')),
            base64_encode($result->get('CiphertextBlob'))
        );
        $output->writeln('Encrypted.');
    }
}
