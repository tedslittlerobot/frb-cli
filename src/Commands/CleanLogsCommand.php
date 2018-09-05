<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentlessCommand;
use Tlr\Frb\Tasks\EnvironmentManager;

class CleanLogsCommand extends AbstractEnvironmentlessCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Clean Logs';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('clean:logs')
            ->setDescription('Clean the log files.')
        ;
    }

    /**
     * Run the command
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $this
            ->task(EnvironmentManager::class)
            ->cleanLogs(frbEnvPath())
        ;
    }
}
