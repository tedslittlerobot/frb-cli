<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentCommand;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\FrbRemote;

class RemoteRunCommand extends AbstractEnvironmentCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Run Server Command';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('remote:run')
            ->setDescription('Run a command against the remote server.')
            ->addEnvironmentArgument()
            ->addArgument('remote-command', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
        ;
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function handle(Config $config, InputInterface $input, OutputInterface $output)
    {
        $command = collect($input->getArgument('remote-command'))->implode(' ');

        $this->task(FrbRemote::class)->run($config, $command);
    }
}
