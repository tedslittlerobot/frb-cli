<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Config;

class SshCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('ssh')
            ->setDescription('SSH into the given environment')
            ->addArgument('environment', InputArgument::REQUIRED)
        ;
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config($input->getArgument('environment'));

        $command = sprintf('ssh %s', $config->sshUrl());

        passthru($command);
    }
}
