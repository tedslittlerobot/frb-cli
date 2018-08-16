<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentlessCommand;
use Tlr\Frb\Tasks\EnvironmentManager;

class MakeEnvironmentCommand extends AbstractEnvironmentlessCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Make Environment';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('make:env')
            ->setDescription('Create a new environment file.')
            ->addArgument('env', InputArgument::REQUIRED, 'Environment to set up.')
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
        $environment = $this->getArgument('env');
        $root = findProjectRoot();

        $this
            ->task(EnvironmentManager::class)
            ->copySampleFileToEnv($root . '.deploy', 'sample.yml', "{$environment}.yml")
        ;
    }
}
