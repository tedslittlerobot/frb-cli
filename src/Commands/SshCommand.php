<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentCommand;
use Tlr\Frb\Config;

class SshCommand extends AbstractEnvironmentCommand
{
    protected $title = 'SSH';

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
            ->addEnvironmentArgument()
        ;
    }

    /**
     * Execute the command.
     *
     * @param  \Tlr\Frb\Config  $config
     * @return void
     */
    protected function handle(Config $config, InputInterface $input, OutputInterface $output)
    {
        $command = sprintf('ssh %s', $config->sshUrl());

        // not my most proud moment, but the easiest way to forward the input
        // and output buffer to the shell...
        passthru($command);
    }
}
